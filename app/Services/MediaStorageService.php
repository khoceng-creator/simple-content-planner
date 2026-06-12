<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\ContentImage;
use App\Models\ContentPlan;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MediaStorageService
{
    public function __construct(private readonly ImageOptimizationService $imageOptimizer) {}

    public function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.media_disk', 'r2'));
    }

    public function storeBrandLogo(User $user, UploadedFile $file): string
    {
        return $this->storeUploadedImage(
            "brands/{$user->id}/logos",
            $file,
            (int) config('media.logo_max_dimension', 512),
        )['key'];
    }

    public function replaceBrandLogo(Brand $brand, UploadedFile $file): string
    {
        return $this->storeBrandLogo($brand->user, $file);
    }

    public function deleteBrandLogo(?string $objectKey): void
    {
        $this->deleteObject($objectKey);
    }

    public function storeContentImage(ContentPlan $contentPlan, UploadedFile $file, int $sortOrder = 0): array
    {
        $storedImage = $this->storeUploadedImage(
            "brands/{$contentPlan->brand_id}/contents/{$contentPlan->id}",
            $file,
            (int) config('media.image_max_dimension', 1920),
        );

        return [
            'file_path' => $storedImage['key'],
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $storedImage['mime_type'],
            'file_size' => $storedImage['file_size'],
            'sort_order' => $sortOrder,
        ];
    }

    public function deleteContentImage(ContentImage $contentImage): void
    {
        $this->deleteObject($contentImage->file_path);
    }

    public function deleteObject(?string $objectKey): void
    {
        if (! $objectKey) {
            return;
        }

        try {
            if ($this->disk()->exists($objectKey) && ! $this->disk()->delete($objectKey)) {
                throw new RuntimeException('Objek media tidak dapat dihapus.');
            }
        } catch (Throwable $exception) {
            Log::error('Media object deletion failed.', [
                'object_key' => $objectKey,
                'exception' => $exception::class,
            ]);

            throw new RuntimeException('Media gagal dihapus dari penyimpanan.', previous: $exception);
        }
    }

    /** @return resource|null */
    public function readStream(string $objectKey)
    {
        return $this->disk()->readStream($objectKey);
    }

    public function publicUrl(string $objectKey): ?string
    {
        if (config('filesystems.media_visibility', 'private') !== 'public') {
            return null;
        }

        $r2Url = config('filesystems.disks.r2.url');
        if (config('filesystems.media_disk') === 'r2' && $r2Url) {
            return rtrim((string) $r2Url, '/').'/'.ltrim($objectKey, '/');
        }

        return $this->disk()->url($objectKey);
    }

    public function displayUrl(ContentImage $contentImage): string
    {
        return $this->publicUrl($contentImage->file_path)
            ?? route('media.show', $contentImage);
    }

    public function displayBrandLogoUrl(Brand $brand): string
    {
        return $this->publicUrl((string) $brand->logo_path)
            ?? route('media.brand-logo', $brand);
    }

    public function mimeTypeFromPath(string $objectKey): string
    {
        return match (strtolower(pathinfo($objectKey, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    /**
     * @return array{key: string, mime_type: string, file_size: int}
     */
    private function storeUploadedImage(string $directory, UploadedFile $file, int $maxDimension): array
    {
        $optimized = $this->imageOptimizer->optimize($file, $maxDimension);
        $key = trim($directory, '/').'/'.Str::uuid().'.'.$optimized['extension'];

        try {
            $stream = fopen('php://temp', 'w+b');
            if ($stream === false) {
                throw new RuntimeException('Stream media tidak dapat dibuat.');
            }

            fwrite($stream, $optimized['contents']);
            rewind($stream);

            if (! $this->disk()->writeStream($key, $stream, [
                'ContentType' => $optimized['mime_type'],
                'CacheControl' => 'public, max-age='.(int) config('media.browser_cache_seconds', 31536000).', immutable',
            ])) {
                throw new RuntimeException('Objek media tidak dapat ditulis.');
            }

            if (is_resource($stream)) {
                fclose($stream);
            }

            return [
                'key' => $key,
                'mime_type' => $optimized['mime_type'],
                'file_size' => $optimized['file_size'],
            ];
        } catch (Throwable $exception) {
            if (isset($stream) && is_resource($stream)) {
                fclose($stream);
            }

            Log::error('Media object write failed.', [
                'directory' => $directory,
                'exception' => $exception::class,
            ]);

            throw new RuntimeException('Media gagal disimpan. Silakan coba lagi.', previous: $exception);
        }
    }
}
