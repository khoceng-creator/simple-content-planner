<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ContentImage;
use App\Models\ContentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['filesystems.media_disk' => 'public', 'filesystems.media_visibility' => 'private']);
    }

    public function test_private_media_requires_authentication_and_owner_authorization(): void
    {
        [$owner, $image] = $this->imageFixture();
        $other = User::factory()->create();

        $this->get(route('media.show', $image))->assertRedirect(route('login'));
        $this->actingAs($other)->get(route('media.show', $image))->assertForbidden();
        $this->actingAs($owner)->get(route('media.show', $image))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('Cache-Control', 'max-age=300, private');
    }

    public function test_missing_media_returns_404(): void
    {
        [$owner, $image] = $this->imageFixture(false);
        $this->actingAs($owner)->get(route('media.show', $image))->assertNotFound();
    }

    public function test_public_mode_uses_configured_r2_url(): void
    {
        Storage::fake('r2');
        config([
            'filesystems.media_disk' => 'r2',
            'filesystems.media_visibility' => 'public',
            'filesystems.disks.r2.url' => 'https://media.example.com',
        ]);
        $image = ContentImage::factory()->make(['file_path' => 'brands/1/contents/2/test.jpg']);

        $this->assertSame('https://media.example.com/brands/1/contents/2/test.jpg', $image->displayUrl());
    }

    private function imageFixture(bool $store = true): array
    {
        $owner = User::factory()->create();
        $brand = Brand::factory()->for($owner)->create();
        $plan = ContentPlan::factory()->for($brand)->create();
        $image = ContentImage::factory()->for($plan)->create(['file_path' => 'brands/1/contents/1/private.jpg']);

        if ($store) {
            Storage::disk('public')->put($image->file_path, 'image-bytes');
        }

        return [$owner, $image];
    }
}
