<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class BrandController extends Controller
{
    public function __construct(private readonly MediaStorageService $media) {}

    public function index(): View
    {
        $brands = request()->user()->brands()
            ->withCount('contentPlans')
            ->latest()
            ->get();

        return view('brands.index', compact('brands'));
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $uploadedKey = null;

        try {
            $brand = DB::transaction(function () use ($request, &$uploadedKey): Brand {
                $name = (string) $request->string('name')->trim();
                $brand = $request->user()->brands()->create([
                    'name' => $name,
                    'slug' => Brand::uniqueSlugFor($request->user(), $name),
                ]);

                if ($request->hasFile('logo')) {
                    $uploadedKey = $this->media->storeBrandLogo($request->user(), $request->file('logo'));
                    $brand->update(['logo_path' => $uploadedKey]);
                }

                return $brand;
            });
        } catch (Throwable $exception) {
            if ($uploadedKey) {
                rescue(fn () => $this->media->deleteObject($uploadedKey), report: true);
            }
            report($exception);

            return back()->withInput()->withErrors(['logo' => 'Brand gagal disimpan. Silakan coba lagi.']);
        }

        return redirect()->route('brands.index')->with('success', "Brand {$brand->name} berhasil dibuat.");
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $newKey = null;
        $oldKey = $brand->logo_path;
        $removeLogo = $request->boolean('remove_logo');

        try {
            if ($request->hasFile('logo')) {
                $newKey = $this->media->replaceBrandLogo($brand, $request->file('logo'));
            }

            DB::transaction(function () use ($request, $brand, $newKey, $removeLogo): void {
                $name = (string) $request->string('name')->trim();
                $brand->update([
                    'name' => $name,
                    'slug' => Brand::uniqueSlugFor($request->user(), $name, $brand),
                    'logo_path' => $newKey ?: ($removeLogo ? null : $brand->logo_path),
                ]);
            });
        } catch (Throwable $exception) {
            if ($newKey) {
                rescue(fn () => $this->media->deleteObject($newKey), report: true);
            }
            report($exception);

            return back()->withInput()->withErrors(['logo' => 'Brand gagal diperbarui. Silakan coba lagi.']);
        }

        if ($oldKey && ($newKey || $removeLogo)) {
            rescue(fn () => $this->media->deleteBrandLogo($oldKey), report: true);
        }

        return back()->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);
        $brand->load('contentPlans.images');

        try {
            DB::transaction(function () use ($brand): void {
                $this->media->deleteBrandLogo($brand->logo_path);
                foreach ($brand->contentPlans as $contentPlan) {
                    foreach ($contentPlan->images as $image) {
                        $this->media->deleteContentImage($image);
                    }
                }
                $brand->delete();
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['brand' => 'Brand gagal dihapus dari penyimpanan.']);
        }

        return redirect()->route('brands.index')->with('success', 'Brand beserta semua kontennya telah dihapus.');
    }
}
