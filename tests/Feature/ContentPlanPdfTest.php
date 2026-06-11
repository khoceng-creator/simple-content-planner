<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ContentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentPlanPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config([
            'filesystems.media_disk' => 'public',
            'filesystems.media_visibility' => 'private',
        ]);
    }

    public function test_preview_page_has_direct_pdf_actions(): void
    {
        [$user, $contentPlan] = $this->contentPlanFixture();

        $this->actingAs($user)
            ->get(route('contents.preview', $contentPlan))
            ->assertOk()
            ->assertSee('Preview PDF')
            ->assertSee('Download PDF')
            ->assertSee(route('contents.pdf.preview', $contentPlan), false)
            ->assertSee(route('contents.pdf.download', $contentPlan), false)
            ->assertDontSee('Print / PDF')
            ->assertDontSee('>Share<', false);
    }

    public function test_owner_can_preview_and_download_a_generated_pdf(): void
    {
        [$user, $contentPlan] = $this->contentPlanFixture();

        $preview = $this->actingAs($user)->get(route('contents.pdf.preview', $contentPlan));
        $preview->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('private', $preview->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $preview->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=0', $preview->headers->get('Cache-Control'));
        $this->assertStringStartsWith('inline;', $preview->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $preview->getContent());

        $download = $this->actingAs($user)->get(route('contents.pdf.download', $contentPlan));
        $download->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('attachment;', $download->headers->get('Content-Disposition'));
        $this->assertStringContainsString('campaign-launch-brief', $download->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $download->getContent());
    }

    public function test_pdf_actions_require_the_content_owner(): void
    {
        [$owner, $contentPlan] = $this->contentPlanFixture();
        $otherUser = User::factory()->create();

        $this->get(route('contents.pdf.preview', $contentPlan))->assertRedirect(route('login'));
        $this->actingAs($otherUser)->get(route('contents.pdf.preview', $contentPlan))->assertForbidden();
        $this->actingAs($otherUser)->get(route('contents.pdf.download', $contentPlan))->assertForbidden();

        $this->actingAs($owner)
            ->get(route('contents.print', $contentPlan))
            ->assertRedirect(route('contents.pdf.preview', $contentPlan));
    }

    private function contentPlanFixture(): array
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->for($user)->create(['name' => 'Invitationery Asia']);
        $contentPlan = ContentPlan::factory()->for($brand)->create([
            'posting_date' => '2026-06-20',
            'posting_time' => '15:00',
            'headline' => 'Campaign Launch Brief',
            'detail_html' => '<p>Susun hook, value, dan call to action.</p>',
            'note_html' => '<p>Pastikan aset sudah mendapat approval.</p>',
            'document_link' => 'https://example.com/campaign-brief',
            'platforms' => ['instagram' => true, 'tiktok' => true],
        ]);
        $imagePath = "brands/{$brand->id}/contents/{$contentPlan->id}/campaign-cover.png";
        Storage::disk('public')->put($imagePath, file_get_contents(public_path('images/IMM.png')));
        $contentPlan->images()->create([
            'file_path' => $imagePath,
            'original_name' => 'campaign-cover.png',
            'mime_type' => 'image/png',
            'file_size' => Storage::disk('public')->size($imagePath),
            'sort_order' => 0,
        ]);

        return [$user, $contentPlan];
    }
}
