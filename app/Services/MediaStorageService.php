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
    public function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.media_disk', 'r2'));
    }

    public function storeBrandLogo(User $user, UploadedFile $file): string
    {
        return $this->storeUploadedFile("brands/{$user->id}/logos", $file);
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
        $key = $this->storeUploadedFile(
            "brands/{$contentPlan->brand_id}/contents/{$contentPlan->id}",
            $file,
        );

        return [
            'file_path' => $key,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'file_size' => $file->getSize(),
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

    public function objectExists(string $objectKey): bool
    {
        return $this->disk()->exists($objectKey);
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

    private function storeUploadedFile(string $directory, UploadedFile $file): string
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $key = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        try {
            $stream = fopen($file->getRealPath(), 'rb');
            if ($stream === false || ! $this->disk()->writeStream($key, $stream)) {
                throw new RuntimeException('Objek media tidak dapat ditulis.');
            }

            if (is_resource($stream)) {
                fclose($stream);
            }

            return $key;
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
