<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ContentPlan;
use App\Models\ContentType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandContentPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['filesystems.media_disk' => 'public', 'filesystems.media_visibility' => 'private']);
    }

    public function test_user_can_create_update_and_delete_own_brand(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('brands.store'), ['name' => 'Brand Saya'])
            ->assertRedirect(route('brands.index'));
        $brand = Brand::query()->firstOrFail();

        $this->actingAs($user)->put(route('brands.update', $brand), ['name' => 'Brand Baru'])
            ->assertRedirect();
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'Brand Baru', 'slug' => 'brand-baru']);

        $this->actingAs($user)->delete(route('brands.destroy', $brand))
            ->assertRedirect(route('brands.index'));
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_flash_message_renders_as_a_dismissible_timed_toast(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['success' => 'Brand berhasil diperbarui.'])
            ->get(route('brands.index'))
            ->assertOk()
            ->assertSee('Brand berhasil diperbarui.')
            ->assertSee('data-toast', false)
            ->assertSee('data-toast-duration="5000"', false)
            ->assertSee('data-toast-close', false)
            ->assertSee('aria-label="Tutup notifikasi"', false);
    }

    public function test_user_cannot_update_another_users_brand(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $brand = Brand::factory()->for($owner)->create();

        $this->actingAs($other)->put(route('brands.update', $brand), ['name' => 'Dicuri'])
            ->assertForbidden();
    }

    public function test_logo_upload_is_stored_as_object_key_and_replacement_deletes_old_object(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('brands.store'), [
            'name' => 'Visual Brand',
            'logo' => UploadedFile::fake()->image('logo.jpg', 200, 200),
        ])->assertRedirect();

        $brand = Brand::query()->firstOrFail();
        $this->assertStringStartsWith("brands/{$user->id}/logos/", $brand->logo_path);
        $this->assertStringNotContainsString('base64', $brand->logo_path);
        Storage::disk('public')->assertExists($brand->logo_path);
        $oldKey = $brand->logo_path;

        $this->actingAs($user)->put(route('brands.update', $brand), [
            'name' => $brand->name,
            'logo' => UploadedFile::fake()->image('new-logo.png', 200, 200),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($oldKey);
        Storage::disk('public')->assertExists($brand->fresh()->logo_path);
    }

    public function test_content_crud_validation_filters_and_sorting_work(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create();
        $payload = [
            'posting_date' => '2026-06-20',
            'posting_time' => '18:30',
            'type' => 'reels',
            'platforms' => ['instagram' => true, 'tiktok' => false],
            'headline' => 'Konten Kedua',
            'detail_html' => '<p onclick="bad()">Detail <strong>aman</strong></p><script>alert(1)</script>',
            'note_html' => '<p>Catatan</p>',
            'document_link' => 'https://example.com/doc',
            'images' => [UploadedFile::fake()->image('content.jpg', 600, 600)],
        ];

        $this->actingAs($user)->post(route('contents.store', $brand), $payload)->assertRedirect();
        $plan = ContentPlan::query()->firstOrFail();
        $this->assertStringNotContainsString('script', $plan->detail_html);
        $this->assertStringNotContainsString('onclick', $plan->detail_html);
        $this->assertDatabaseCount('content_images', 1);
        Storage::disk('public')->assertExists($plan->images()->first()->file_path);

        ContentPlan::factory()->for($brand)->create([
            'posting_date' => '2026-06-10',
            'posting_time' => '09:00',
            'type' => 'carousel',
            'headline' => 'Konten Pertama',
        ]);
        ContentPlan::factory()->for($brand)->create([
            'posting_date' => '2026-07-01',
            'headline' => 'Bulan Lain',
        ]);

        $this->actingAs($user)->get(route('brands.workspace', ['brand' => $brand, 'year' => 2026, 'month' => 6]))
            ->assertOk()
            ->assertSeeInOrder(['Konten Pertama', 'Konten Kedua'])
            ->assertDontSee('Bulan Lain');

        $this->actingAs($user)->get(route('brands.workspace', ['brand' => $brand, 'year' => 2026, 'month' => 6, 'type' => 'reels']))
            ->assertSee('Konten Kedua')
            ->assertDontSee('<div class="headline">Konten Pertama</div>', false);

        $this->actingAs($user)->get(route('brands.workspace', ['brand' => $brand, 'year' => 2026, 'month' => 6, 'view' => 'feed']))
            ->assertSeeInOrder(['Konten Kedua', 'Konten Pertama']);

        $this->actingAs($user)->patch(route('contents.toggle-made', $plan))->assertRedirect();
        $this->assertTrue($plan->fresh()->is_made);

        $key = $plan->images()->first()->file_path;
        $this->actingAs($user)->delete(route('contents.destroy', $plan))->assertRedirect();
        Storage::disk('public')->assertMissing($key);
    }

    public function test_user_can_create_and_reuse_a_custom_content_type_for_one_brand(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create();
        $otherBrand = Brand::factory()->for($user)->create();
        $payload = [
            'posting_date' => '2026-06-18',
            'posting_time' => '10:00',
            'type' => '__new',
            'new_type' => 'UGC Video',
            'platforms' => ['instagram' => true, 'tiktok' => false],
            'headline' => 'Konten UGC Pertama',
        ];

        $this->assertSame(3, $brand->contentTypes()->where('is_default', true)->count());

        $this->actingAs($user)
            ->post(route('contents.store', $brand), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('content_types', [
            'brand_id' => $brand->id,
            'name' => 'UGC Video',
            'slug' => 'ugc-video',
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('content_plans', [
            'brand_id' => $brand->id,
            'headline' => 'Konten UGC Pertama',
            'type' => 'ugc-video',
        ]);

        $payload['type'] = 'ugc-video';
        $payload['new_type'] = null;
        $payload['headline'] = 'Konten UGC Kedua';
        $this->actingAs($user)
            ->post(route('contents.store', $brand), $payload)
            ->assertRedirect();

        $this->assertSame(1, ContentType::query()
            ->where('brand_id', $brand->id)
            ->where('slug', 'ugc-video')
            ->count());

        $this->actingAs($user)
            ->get(route('brands.workspace', [
                'brand' => $brand,
                'year' => 2026,
                'month' => 6,
                'type' => 'ugc-video',
            ]))
            ->assertOk()
            ->assertSee('UGC Video')
            ->assertSee('Konten UGC Pertama')
            ->assertSee('Konten UGC Kedua');

        $payload['headline'] = 'Tidak boleh lintas brand';
        $this->actingAs($user)
            ->post(route('contents.store', $otherBrand), $payload)
            ->assertSessionHasErrors('type');
    }

    public function test_calendar_shows_tomorrow_reminder_for_the_closest_upcoming_content(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::create(2026, 6, 10, 10, 0, 0, 'Asia/Jakarta'),
        );

        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create();
        ContentPlan::factory()->for($brand)->create([
            'posting_date' => '2026-06-11',
            'posting_time' => '18:30',
            'headline' => 'Konten Besok',
        ]);
        ContentPlan::factory()->for($brand)->create([
            'posting_date' => '2026-06-14',
            'posting_time' => '09:00',
            'headline' => 'Konten Berikutnya',
        ]);

        $this->actingAs($user)
            ->get(route('brands.workspace', ['brand' => $brand, 'year' => 2026, 'month' => 6]))
            ->assertOk()
            ->assertSee('Jadwal terdekat besok pukul 18.30.')
            ->assertDontSee('Besok ada konten Konten Besok.')
            ->assertDontSee('Jadwal terdekat 4 hari lagi');

        CarbonImmutable::setTestNow();
    }

    public function test_content_rejects_invalid_type_no_platform_and_foreign_brand(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create();
        $payload = [
            'posting_date' => '2026-06-10',
            'type' => 'story',
            'platforms' => ['instagram' => false, 'tiktok' => false],
            'headline' => 'Invalid',
        ];

        $this->actingAs($user)->post(route('contents.store', $brand), $payload)
            ->assertSessionHasErrors(['type', 'platforms']);

        $foreignBrand = Brand::factory()->create();
        $payload['type'] = 'single';
        $payload['platforms']['instagram'] = true;
        $this->actingAs($user)->post(route('contents.store', $foreignBrand), $payload)->assertForbidden();
    }

    public function test_user_can_update_own_content_but_not_another_users_content(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $plan = ContentPlan::factory()->for(Brand::factory()->for($owner))->create();
        $payload = [
            'posting_date' => '2026-06-15',
            'posting_time' => '10:30',
            'type' => 'single',
            'platforms' => ['instagram' => true, 'tiktok' => true],
            'headline' => 'Headline Diperbarui',
        ];

        $this->actingAs($owner)->put(route('contents.update', $plan), $payload)->assertRedirect();
        $this->assertDatabaseHas('content_plans', ['id' => $plan->id, 'headline' => 'Headline Diperbarui']);

        $this->actingAs($other)->put(route('contents.update', $plan), $payload)->assertForbidden();
    }

    public function test_invalid_and_oversized_uploads_are_rejected(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create();

        $this->actingAs($user)->post(route('brands.store'), [
            'name' => 'Bad Logo',
            'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $this->actingAs($user)->post(route('contents.store', $brand), [
            'posting_date' => '2026-06-15',
            'type' => 'single',
            'platforms' => ['instagram' => true, 'tiktok' => false],
            'headline' => 'Upload besar',
            'images' => [UploadedFile::fake()->image('large.jpg')->size(6000)],
        ])->assertSessionHasErrors('images.0');
    }

    public function test_deleting_brand_cascades_records_and_nested_media(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create(['logo_path' => 'brands/1/logos/logo.jpg']);
        $plan = ContentPlan::factory()->for($brand)->create();
        $image = $plan->images()->create([
            'file_path' => "brands/{$brand->id}/contents/{$plan->id}/image.jpg",
            'original_name' => 'image.jpg', 'mime_type' => 'image/jpeg', 'file_size' => 10, 'sort_order' => 0,
        ]);
        Storage::disk('public')->put($brand->logo_path, 'logo');
        Storage::disk('public')->put($image->file_path, 'image');

        $this->actingAs($user)->delete(route('brands.destroy', $brand))->assertRedirect();

        $this->assertDatabaseMissing('content_plans', ['id' => $plan->id]);
        $this->assertDatabaseMissing('content_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($brand->logo_path);
        Storage::disk('public')->assertMissing($image->file_path);
    }
}
