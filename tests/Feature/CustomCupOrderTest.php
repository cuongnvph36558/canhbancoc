<?php

namespace Tests\Feature;

use App\Events\NewOrderCreated;
use App\Models\CustomCupOrder;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomCupOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customizer_shows_draggable_cup_design_controls(): void
    {
        $this->withoutVite();

        $this->get(route('products.customize'))
            ->assertOk()
            ->assertSee('Thiết kế cốc của bạn')
            ->assertSee('data-print-area', false)
            ->assertSee('Quai đen – lòng đen')
            ->assertSee('Quai hồng – lòng hồng')
            ->assertSee('data-add-text', false)
            ->assertSee('data-artwork-input', false)
            ->assertSee('data-remove-background', false)
            ->assertSee('data-restore-background', false)
            ->assertSee('data-background-removal-status', false)
            ->assertSee('data-selected-font-size', false)
            ->assertSee('min="50" max="200"', false)
            ->assertSee('không gửi lên dịch vụ xử lý ảnh')
            ->assertSee('data-cup-quantity', false)
            ->assertSee('customer_name', false)
            ->assertSee('data-vietnam-address', false)
            ->assertSee('name="shipping_province"', false)
            ->assertSee('name="shipping_ward"', false);
    }

    public function test_guest_can_place_a_cup_order_and_receive_a_confirmation(): void
    {
        $this->withoutVite();
        Queue::fake();

        $response = $this->post(route('custom-cup-orders.store'), $this->validOrder());

        $order = CustomCupOrder::query()->firstOrFail();
        Queue::assertPushed(
            BroadcastEvent::class,
            fn (BroadcastEvent $job): bool => $job->event instanceof NewOrderCreated
                && $job->event->orderType === 'custom'
                && $job->event->orderNumber === $order->order_number
                && $job->event->customerName === $order->customer_name
                && $job->event->totalAmount === 89000
                && $job->event->url === route('admin.custom-cup-orders.show', $order),
        );

        $response
            ->assertRedirect(route('custom-cup-orders.confirmation'))
            ->assertSessionHas('order_number', $order->order_number);
        $this->assertNull($order->user_id);
        $this->assertSame('CM-'.now()->format('ymd'), substr($order->order_number, 0, 9));
        $this->assertSame('Mai Nguyễn', $order->customer_name);
        $this->assertSame('black', $order->cup_variant);
        $this->assertSame('11oz', $order->cup_size);
        $this->assertSame(1, $order->quantity);
        $this->assertSame(89000, $order->total_amount);
        $this->assertSame(0, $order->discount_percentage);
        $this->assertSame('pending', $order->status);
        $this->assertSame('Thành phố Hà Nội', $order->shipping_province);
        $this->assertSame('Phường Ba Đình', $order->shipping_ward);
        $this->assertSame('Mai Nguyễn', $order->design['items'][0]['content']);

        $this->get(route('custom-cup-orders.confirmation'))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->get(route('custom-cup-orders.preview', $order))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('Mai Nguyễn');
    }

    public function test_selected_text_size_is_saved_and_used_in_the_fallback_preview(): void
    {
        $this->post(route('custom-cup-orders.store'), $this->validOrder([
            'design' => [
                'items' => [
                    [
                        'type' => 'text',
                        'content' => 'Mai Nguyễn',
                        'x' => 20,
                        'y' => 40,
                        'width' => 60,
                        'font_size' => 150,
                    ],
                ],
            ],
        ]))
            ->assertRedirect(route('custom-cup-orders.confirmation'));

        $order = CustomCupOrder::query()->firstOrFail();

        $this->assertSame(150, $order->design['items'][0]['font_size']);

        $this->get(route('custom-cup-orders.preview', $order))
            ->assertOk()
            ->assertSee('font-size="108"', false);
    }

    public function test_authenticated_customer_can_reuse_registered_details_for_a_custom_cup_order(): void
    {
        $user = User::factory()->create([
            'phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
        ]);

        $this->actingAs($user)
            ->get(route('products.customize'))
            ->assertOk()
            ->assertSee('value="0901234567"', false)
            ->assertSee('data-selected-province="Thành phố Hà Nội"', false)
            ->assertSee('data-selected-ward="Phường Ba Đình"', false)
            ->assertSee('12 Phố Huế');

        $this->actingAs($user)
            ->post(route('custom-cup-orders.store'), $this->validOrder())
            ->assertRedirect(route('custom-cup-orders.confirmation'));

        $this->assertDatabaseHas('custom_cup_orders', [
            'user_id' => $user->id,
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
        ]);
    }

    public function test_orders_of_five_or_more_cups_receive_the_bulk_discount(): void
    {
        $this->post(route('custom-cup-orders.store'), $this->validOrder(['quantity' => 5]))
            ->assertRedirect(route('custom-cup-orders.confirmation'));

        $this->assertDatabaseHas('custom_cup_orders', [
            'quantity' => 5,
            'unit_price' => 89000,
            'discount_percentage' => 10,
            'total_amount' => 400500,
        ]);
    }

    public function test_uploaded_customer_artwork_is_stored_on_the_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $payload = $this->validOrder([
            'design' => [
                'items' => [
                    [
                        'type' => 'image',
                        'content' => 'artwork',
                        'x' => 30,
                        'y' => 35,
                        'width' => 38,
                    ],
                ],
            ],
            'artwork' => UploadedFile::fake()->image('artwork.png'),
        ]);

        $this->post(route('custom-cup-orders.store'), $payload)
            ->assertRedirect(route('custom-cup-orders.confirmation'));

        $order = CustomCupOrder::query()->firstOrFail();

        $this->assertNotNull($order->artwork_path);
        Storage::disk('local')->assertExists($order->artwork_path);
        Storage::disk('public')->assertMissing($order->artwork_path);
    }

    public function test_custom_cup_order_saves_generated_preview_when_browser_sends_it(): void
    {
        $this->withoutVite();
        Storage::fake('local');

        $this->post(route('custom-cup-orders.store'), $this->validOrder([
            'design_preview' => 'data:image/png;base64,'.base64_encode('preview-content'),
        ]))
            ->assertRedirect(route('custom-cup-orders.confirmation'));

        $order = CustomCupOrder::query()->firstOrFail();

        $this->assertNotNull($order->preview_path);
        Storage::disk('local')->assertExists($order->preview_path);

        $this->get(route('custom-cup-orders.confirmation'))
            ->assertOk()
            ->assertSee('src="'.route('custom-cup-orders.preview', $order).'"', false);
    }

    public function test_invalid_custom_cup_order_does_not_create_an_order(): void
    {
        $this->from(route('products.customize'))
            ->post(route('custom-cup-orders.store'), $this->validOrder([
                'cup_variant' => 'gold',
                'quantity' => 0,
                'design' => [
                    'items' => [
                        [
                            'type' => 'icon',
                            'content' => '<script>alert(1)</script>',
                            'x' => 10,
                            'y' => 20,
                            'width' => 20,
                            'font_size' => 201,
                        ],
                    ],
                ],
            ]))
            ->assertRedirect(route('products.customize'))
            ->assertSessionHasErrors([
                'cup_variant',
                'quantity',
                'design.items.0.content',
                'design.items.0.font_size',
            ]);

        $this->assertDatabaseCount('custom_cup_orders', 0);
    }

    public function test_custom_cup_image_layer_requires_an_uploaded_image(): void
    {
        $this->from(route('products.customize'))
            ->post(route('custom-cup-orders.store'), $this->validOrder([
                'design' => [
                    'items' => [
                        [
                            'type' => 'image',
                            'content' => 'artwork',
                            'x' => 20,
                            'y' => 25,
                            'width' => 38,
                        ],
                    ],
                ],
            ]))
            ->assertRedirect(route('products.customize'))
            ->assertSessionHasErrors('artwork');

        $this->assertDatabaseCount('custom_cup_orders', 0);
    }

    public function test_only_an_active_admin_can_view_orders_and_download_customer_artwork(): void
    {
        Storage::fake('local');
        $artworkPath = UploadedFile::fake()
            ->image('artwork.png')
            ->store('custom-cup-artwork', 'local');
        $orderData = $this->validOrder();
        $order = CustomCupOrder::create([
            'order_number' => 'CM-261009-ABCDEFG',
            'customer_name' => $orderData['customer_name'],
            'customer_phone' => $orderData['customer_phone'],
            'customer_email' => $orderData['customer_email'],
            'shipping_province' => $orderData['shipping_province'],
            'shipping_ward' => $orderData['shipping_ward'],
            'shipping_address' => $orderData['shipping_address'],
            'cup_variant' => $orderData['cup_variant'],
            'cup_size' => '11oz',
            'quantity' => 1,
            'unit_price' => 89000,
            'discount_percentage' => 0,
            'total_amount' => 89000,
            'design' => $orderData['design'],
            'artwork_path' => $artworkPath,
            'status' => 'pending',
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('admin.custom-cup-orders.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($client)
            ->get(route('admin.custom-cup-orders.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.custom-cup-orders.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Mai Nguyễn')
            ->assertSee('src="'.route('admin.custom-cup-orders.preview', $order).'"', false);

        $this->actingAs($admin)
            ->get(route('admin.custom-cup-orders.preview', $order))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('Mai Nguyễn');

        $this->actingAs($admin)
            ->get(route('admin.custom-cup-orders.show', $order))
            ->assertOk()
            ->assertSee('Thiết kế in trên cốc')
            ->assertSee('Phường Ba Đình')
            ->assertSee('Tải hình in khách gửi');

        $this->actingAs($admin)
            ->get(route('admin.custom-cup-orders.artwork', $order))
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validOrder(array $overrides = []): array
    {
        return array_replace_recursive([
            'customer_name' => 'Mai Nguyễn',
            'customer_phone' => '0901234567',
            'customer_email' => 'mai@example.com',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '42 Phố Huế',
            'cup_variant' => 'black',
            'quantity' => 1,
            'design' => [
                'items' => [
                    [
                        'type' => 'text',
                        'content' => 'Mai Nguyễn',
                        'x' => 20,
                        'y' => 40,
                        'width' => 60,
                        'font_size' => 100,
                    ],
                    [
                        'type' => 'icon',
                        'content' => '💗',
                        'x' => 40,
                        'y' => 65,
                        'width' => 20,
                        'font_size' => 100,
                    ],
                ],
            ],
        ], $overrides);
    }
}
