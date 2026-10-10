<?php

namespace Tests\Feature;

use App\Models\SitePage;
use App\Models\User;
use App\Services\SitePageContent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_clients_cannot_edit_page_content(): void
    {
        $this->get(route('admin.pages.edit', 'home'))
            ->assertRedirect('/dang-nhap');

        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.pages.edit', 'home'))
            ->assertForbidden();
    }

    public function test_admin_can_open_both_page_editors(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pages.edit', 'home'))
            ->assertOk()
            ->assertSee('Chỉnh sửa trang Trang chủ')
            ->assertSee('Nội dung giới thiệu')
            ->assertSee('type="file"', false)
            ->assertSee('enctype="multipart/form-data"', false);

        $this->get(route('admin.pages.edit', 'about'))
            ->assertOk()
            ->assertSee('Chỉnh sửa trang Giới thiệu')
            ->assertSee('Nội dung trang chủ');

        $this->get(route('admin.pages.edit', 'header_footer'))
            ->assertOk()
            ->assertSee('Chỉnh sửa trang Header & Footer')
            ->assertSee('Header & Footer');
    }

    public function test_admin_can_update_homepage_content_and_public_page_escapes_it(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->defaultPageContent('home');
        $content['hero_title_first'] = '<script>alert("xss")</script>';
        $content['hero_description'] = 'Mô tả trang chủ đã cập nhật.';
        $image = $this->fakeImage('home.png');

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'home'), [
                'content' => [...$content, 'custom_image_url' => $image],
            ])
            ->assertRedirect(route('admin.pages.edit', 'home'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('site_pages', ['slug' => 'home']);
        $savedContent = SitePage::query()->where('slug', 'home')->firstOrFail()->content;
        $this->assertSame($content['hero_title_first'], $savedContent['hero_title_first']);
        Storage::disk('public')->assertExists($savedContent['custom_image_url']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mô tả trang chủ đã cập nhật.')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee(Storage::disk('public')->url($savedContent['custom_image_url']), false);
    }

    public function test_admin_can_update_about_content_and_public_page_displays_it(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->defaultPageContent('about');
        $content['intro_description'] = 'Câu chuyện thương hiệu mới.';
        $content['value_two_title'] = 'Chăm chút theo cách mới';
        $image = $this->fakeImage('about.png');

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'about'), [
                'content' => [...$content, 'feature_image_url' => $image],
            ])
            ->assertRedirect(route('admin.pages.edit', 'about'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('site_pages', ['slug' => 'about']);
        $savedContent = SitePage::query()->where('slug', 'about')->firstOrFail()->content;
        Storage::disk('public')->assertExists($savedContent['feature_image_url']);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Câu chuyện thương hiệu mới.')
            ->assertSee('Chăm chút theo cách mới')
            ->assertSee(Storage::disk('public')->url($savedContent['feature_image_url']), false);

        $this->put(route('admin.pages.update', 'about'), [
            'content' => [
                ...$content,
                'intro_description' => 'Câu chuyện đã đổi nhưng vẫn giữ ảnh cũ.',
            ],
        ])->assertSessionHas('success');

        $this->assertSame(
            $savedContent['feature_image_url'],
            SitePage::query()->where('slug', 'about')->firstOrFail()->content['feature_image_url'],
        );
        Storage::disk('public')->assertExists($savedContent['feature_image_url']);
    }

    public function test_admin_can_update_header_and_footer_content_and_store_layout_uses_it(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->defaultPageContent('header_footer');
        $content['announcement_text'] = 'Miễn phí ship cho đơn từ 500.000đ';
        $content['brand_name'] = 'Cốc Mơ';
        $content['footer_description'] = 'Mỗi chiếc cốc đều được làm bằng sự yêu thương.';
        $content['footer_email'] = 'hello@cocmo.vn';
        $image = $this->fakeImage('logo.png');

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'header_footer'), [
                'content' => [...$content, 'logo_image_url' => $image],
            ])
            ->assertRedirect(route('admin.pages.edit', 'header_footer'))
            ->assertSessionHas('success');

        $savedContent = SitePage::query()->where('slug', 'header_footer')->firstOrFail()->content;
        Storage::disk('public')->assertExists($savedContent['logo_image_url']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Miễn phí ship cho đơn từ 500.000đ')
            ->assertSee('Cốc Mơ')
            ->assertSee('Mỗi chiếc cốc đều được làm bằng sự yêu thương.')
            ->assertSee('hello@cocmo.vn', false);
    }

    public function test_update_rejects_missing_and_unconfigured_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->defaultPageContent('home');

        unset($content['hero_title_first']);

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'home'), ['content' => $content])
            ->assertSessionHasErrors('content.hero_title_first');

        $content = $this->defaultPageContent('home');
        $content['not_a_page_field'] = 'unexpected';

        $this->put(route('admin.pages.update', 'home'), ['content' => $content])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('site_pages', 0);
    }

    public function test_image_upload_rejects_unsupported_file_types_and_oversized_files(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->defaultPageContent('home');

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'home'), [
                'content' => [...$content, 'custom_image_url' => UploadedFile::fake()->create('invalid.txt', 10, 'text/plain')],
            ])
            ->assertSessionHasErrors('content.custom_image_url');

        $this->put(route('admin.pages.update', 'home'), [
            'content' => [...$content, 'custom_image_url' => $this->fakeImage('oversized.png')->size(5121)],
        ])
            ->assertSessionHasErrors('content.custom_image_url');

        $this->assertDatabaseCount('site_pages', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('pages'));
    }

    public function test_replacing_a_page_image_removes_the_previous_uploaded_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->defaultPageContent('home');
        $previousPath = $this->fakeImage('previous.png')->store('pages/home', 'public');
        SitePage::query()->create([
            'slug' => 'home',
            'content' => [...$content, 'custom_image_url' => $previousPath],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'home'), [
                'content' => [...$content, 'custom_image_url' => $this->fakeImage('replacement.png')],
            ])
            ->assertSessionHas('success');

        $replacementPath = SitePage::query()->where('slug', 'home')->firstOrFail()->content['custom_image_url'];
        $this->assertNotSame($previousPath, $replacementPath);
        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists($replacementPath);
    }

    public function test_unknown_page_cannot_be_opened_for_editing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pages.edit', 'unknown'))
            ->assertNotFound();
    }

    public function test_page_migration_repairs_legacy_table_and_preserves_existing_rows(): void
    {
        Schema::drop('site_pages');
        Schema::create('site_pages', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
        \DB::table('site_pages')->insert([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_10_003943_add_slug_and_content_to_existing_site_pages_table.php');
        $migration->up();
        $migration->up();

        $legacyPage = SitePage::query()->where('slug', 'legacy-1')->firstOrFail();
        $this->assertSame([], $legacyPage->content);

        app(SitePageContent::class)->save('about', ['intro_title_first' => 'Câu chuyện mới']);

        $this->assertSame('Câu chuyện mới', SitePage::query()
            ->where('slug', 'about')
            ->firstOrFail()
            ->content['intro_title_first']);
    }

    /**
     * @return array<string, string>
     */
    private function defaultPageContent(string $slug): array
    {
        $content = [];

        foreach (config('pages.'.$slug.'.sections') as $section) {
            foreach ($section['fields'] as $key => $field) {
                if (($field['type'] ?? 'text') !== 'image') {
                    $content[$key] = $field['default'];
                }
            }
        }

        return $content;
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'),
        );
    }
}
