<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentPlanRequest;
use App\Http\Requests\UpdateContentPlanRequest;
use App\Models\Brand;
use App\Models\ContentPlan;
use App\Models\ContentType;
use App\Services\MediaStorageService;
use App\Services\RichTextSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ContentPlanController extends Controller
{
    public function __construct(
        private readonly RichTextSanitizer $sanitizer,
        private readonly MediaStorageService $media,
    ) {}

    public function store(StoreContentPlanRequest $request, Brand $brand): RedirectResponse
    {
        $uploadedKeys = [];

        try {
            $contentPlan = DB::transaction(function () use ($request, $brand, &$uploadedKeys): ContentPlan {
                $validated = $request->validated();
                $contentType = ContentType::resolveFor($brand, $validated['type'], $validated['new_type'] ?? null);
                $validated['type'] = $contentType->slug;
                $contentPlan = $brand->contentPlans()->create($this->payload($validated));

                foreach ($request->file('images', []) as $sortOrder => $file) {
                    $metadata = $this->media->storeContentImage($contentPlan, $file, $sortOrder);
                    $uploadedKeys[] = $metadata['file_path'];
                    $contentPlan->images()->create($metadata);
                }

                return $contentPlan;
            });
        } catch (Throwable $exception) {
            foreach ($uploadedKeys as $key) {
                rescue(fn () => $this->media->deleteObject($key), report: true);
            }
            report($exception);

            return back()->withInput()->withErrors([
                'images' => 'Upload media gagal sehingga konten tidak disimpan. Periksa koneksi penyimpanan lalu coba lagi.',
            ]);
        }

        return redirect()->to($this->workspaceUrl($brand, $contentPlan))
            ->with('success', 'Konten berhasil ditambahkan.');
    }

    public function update(UpdateContentPlanRequest $request, ContentPlan $contentPlan): RedirectResponse
    {
        $contentPlan->load('images', 'brand');
        $uploadedKeys = [];

        try {
            DB::transaction(function () use ($request, $contentPlan, &$uploadedKeys): void {
                $validated = $request->validated();
                $contentType = ContentType::resolveFor(
                    $contentPlan->brand,
                    $validated['type'],
                    $validated['new_type'] ?? null,
                );
                $validated['type'] = $contentType->slug;
                $contentPlan->update($this->payload($validated));

                $retainedIds = collect($request->input('retain_images', []))->map(fn ($id) => (int) $id);
                foreach ($contentPlan->images->whereNotIn('id', $retainedIds) as $image) {
                    $this->media->deleteContentImage($image);
                    $image->delete();
                }

                $sortOrder = $retainedIds->count();
                foreach ($request->file('images', []) as $file) {
                    $metadata = $this->media->storeContentImage($contentPlan, $file, $sortOrder++);
                    $uploadedKeys[] = $metadata['file_path'];
                    $contentPlan->images()->create($metadata);
                }
            });
        } catch (Throwable $exception) {
            foreach ($uploadedKeys as $key) {
                rescue(fn () => $this->media->deleteObject($key), report: true);
            }
            report($exception);

            return back()->withInput()->withErrors([
                'images' => 'Upload media gagal sehingga perubahan konten dibatalkan. Silakan coba lagi.',
            ]);
        }

        return redirect()->to($this->workspaceUrl($contentPlan->brand, $contentPlan))
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(ContentPlan $contentPlan): RedirectResponse
    {
        $this->authorize('delete', $contentPlan);
        $contentPlan->load('images', 'brand');
        $brand = $contentPlan->brand;

        try {
            DB::transaction(function () use ($contentPlan): void {
                foreach ($contentPlan->images as $image) {
                    $this->media->deleteContentImage($image);
                }
                $contentPlan->delete();
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['content' => 'Konten gagal dihapus dari penyimpanan.']);
        }

        return redirect()->route('brands.workspace', $brand)->with('success', 'Konten berhasil dihapus.');
    }

    private function payload(array $validated): array
    {
        return [
            'posting_date' => $validated['posting_date'],
            'posting_time' => $validated['posting_time'] ?: null,
            'type' => $validated['type'],
            'platforms' => $validated['platforms'],
            'headline' => $validated['headline'],
            'detail_html' => $this->sanitizer->sanitize($validated['detail_html'] ?? null),
            'note_html' => $this->sanitizer->sanitize($validated['note_html'] ?? null),
            'document_link' => $validated['document_link'] ?? null,
        ];
    }

    private function workspaceUrl(Brand $brand, ContentPlan $contentPlan): string
    {
        return route('brands.workspace', [
            'brand' => $brand,
            'year' => $contentPlan->posting_date->year,
            'month' => $contentPlan->posting_date->month,
        ]);
    }
}
