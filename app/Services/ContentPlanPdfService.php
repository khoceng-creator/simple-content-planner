<?php

namespace App\Services;

use App\Models\ContentImage;
use App\Models\ContentPlan;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContentPlanPdfService
{
    public function __construct(private readonly MediaStorageService $mediaStorage) {}

    public function render(ContentPlan $contentPlan): string
    {
        $contentPlan->loadMissing('brand.contentTypes', 'images');

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('contents.pdf', [
            'contentPlan' => $contentPlan,
            'logoDataUri' => $this->localImageDataUri(public_path('images/IMM.png')),
            'brandLogoDataUri' => $this->brandLogoDataUri($contentPlan),
            'pdfImages' => $contentPlan->images
                ->map(fn (ContentImage $image): array => [
                    'name' => $image->original_name,
                    'src' => $this->storedImageDataUri($image->file_path, $image->mime_type),
                ])
                ->filter(fn (array $image): bool => $image['src'] !== null)
                ->values(),
        ])->render());
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function brandLogoDataUri(ContentPlan $contentPlan): ?string
    {
        if (! $contentPlan->brand->logo_path) {
            return null;
        }

        return $this->storedImageDataUri($contentPlan->brand->logo_path);
    }

    private function storedImageDataUri(string $objectKey, ?string $mimeType = null): ?string
    {
        try {
            $stream = $this->mediaStorage->readStream($objectKey);
            if (! is_resource($stream)) {
                return null;
            }

            $contents = stream_get_contents($stream);
            fclose($stream);

            if ($contents === false || $contents === '') {
                return null;
            }

            return $this->dataUri($contents, $mimeType ?: $this->mimeTypeFromPath($objectKey));
        } catch (Throwable $exception) {
            Log::warning('Media could not be embedded in content plan PDF.', [
                'object_key' => $objectKey,
                'exception' => $exception::class,
            ]);

            return null;
        }
    }

    private function localImageDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents === false ? null : $this->dataUri($contents, $this->mimeTypeFromPath($path));
    }

    private function dataUri(string $contents, string $mimeType): string
    {
        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    private function mimeTypeFromPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
