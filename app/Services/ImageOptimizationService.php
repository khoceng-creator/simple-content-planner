<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;

class ImageOptimizationService
{
    /**
     * @return array{contents: string, extension: string, mime_type: string, file_size: int}
     */
    public function optimize(UploadedFile $file, int $maxDimension): array
    {
        $sourceContents = file_get_contents($file->getRealPath());
        $sourceMimeType = $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream';
        $sourceExtension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');

        if ($sourceContents === false || ! function_exists('imagewebp')) {
            return $this->original($file, $sourceContents ?: '', $sourceExtension, $sourceMimeType);
        }

        $source = @imagecreatefromstring($sourceContents);
        if (! $source instanceof GdImage) {
            return $this->original($file, $sourceContents, $sourceExtension, $sourceMimeType);
        }

        $source = $this->orient($source, $file, $sourceMimeType);
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        $encoded = imagewebp($target, null, max(1, min(100, (int) config('media.webp_quality', 82))));
        $optimizedContents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        if (! $encoded || ! is_string($optimizedContents) || $optimizedContents === '') {
            return $this->original($file, $sourceContents, $sourceExtension, $sourceMimeType);
        }

        if ($scale === 1.0 && strlen($optimizedContents) >= strlen($sourceContents)) {
            return $this->original($file, $sourceContents, $sourceExtension, $sourceMimeType);
        }

        return [
            'contents' => $optimizedContents,
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'file_size' => strlen($optimizedContents),
        ];
    }

    private function orient(GdImage $image, UploadedFile $file, string $mimeType): GdImage
    {
        if ($mimeType !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
        $oriented = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($oriented instanceof GdImage && $oriented !== $image) {
            imagedestroy($image);
        }

        return $oriented instanceof GdImage ? $oriented : $image;
    }

    /**
     * @return array{contents: string, extension: string, mime_type: string, file_size: int}
     */
    private function original(
        UploadedFile $file,
        string $contents,
        string $extension,
        string $mimeType,
    ): array {
        return [
            'contents' => $contents,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => strlen($contents) ?: (int) $file->getSize(),
        ];
    }
}
