<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_multiple_uploaded_images(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Bình gốm',
            'sku' => 'BINH-001',
            'price' => 250000,
            'stock' => 5,
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('side.png'),
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::query()->where('sku', 'BINH-001')->firstOrFail();
        $images = $product->images()->get();

        $this->assertCount(2, $images);
        $this->assertTrue($images[0]->is_primary);
        $this->assertFalse($images[1]->is_primary);
        $this->assertSame(0, $images[0]->position);
        Storage::disk('public')->assertExists($images[0]->image_url);
        Storage::disk('public')->assertExists($images[1]->image_url);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee($images[0]->url(), false);

        $this->actingAs($admin)
            ->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee($images[0]->url(), false)
            ->assertSee($images[1]->url(), false);
    }

    public function test_admin_can_remove_and_add_images_without_losing_the_remaining_gallery(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $firstPath = UploadedFile::fake()->image('first.jpg')->store('products', 'public');
        $secondPath = UploadedFile::fake()->image('second.jpg')->store('products', 'public');
        $firstImage = $product->images()->create([
            'image_url' => $firstPath,
            'position' => 0,
            'is_primary' => true,
        ]);
        $secondImage = $product->images()->create([
            'image_url' => $secondPath,
            'position' => 1,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'stock' => $product->stock,
            'remove_image_ids' => [$firstImage->id],
            'images' => [UploadedFile::fake()->image('new.jpg')],
        ]);

        $response->assertRedirect(route('admin.products.show', $product));
        $this->assertDatabaseMissing('product_images', ['id' => $firstImage->id]);
        $this->assertDatabaseHas('product_images', ['id' => $secondImage->id, 'is_primary' => true]);
        $this->assertDatabaseCount('product_images', 2);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
        Storage::disk('public')->assertExists($product->fresh()->images()->latest('id')->firstOrFail()->image_url);
    }

    public function test_admin_cannot_remove_an_image_owned_by_another_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();
        $otherImage = $otherProduct->images()->create([
            'image_url' => 'https://example.com/other.jpg',
            'position' => 0,
            'is_primary' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'stock' => $product->stock,
                'remove_image_ids' => [$otherImage->id],
            ])
            ->assertSessionHasErrors('remove_image_ids.0');

        $this->assertDatabaseHas('product_images', ['id' => $otherImage->id]);
    }

    public function test_invalid_uploaded_image_does_not_create_a_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Bình gốm',
                'sku' => 'BINH-INVALID',
                'price' => 250000,
                'stock' => 5,
                'images' => [UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain')],
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('images.0');

        $this->assertDatabaseMissing('products', ['sku' => 'BINH-INVALID']);
        $this->assertDatabaseCount('product_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }
}
