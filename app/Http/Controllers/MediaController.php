<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ContentImage;
use App\Services\MediaStorageService;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function __construct(private readonly MediaStorageService $media) {}

    public function show(ContentImage $contentImage): StreamedResponse
    {
        $contentImage->load('contentPlan.brand');
        $this->authorize('view', $contentImage->contentPlan);

        return $this->stream(
            $contentImage->file_path,
            $contentImage->mime_type,
            $contentImage->original_name,
        );
    }

    public function brandLogo(Brand $brand): StreamedResponse
    {
        $this->authorize('view', $brand);
        abort_unless($brand->logo_path, 404);
        abort_unless($this->media->objectExists($brand->logo_path), 404);

        return $this->stream(
            $brand->logo_path,
            $this->media->disk()->mimeType($brand->logo_path) ?: 'application/octet-stream',
            $brand->slug.'-logo',
        );
    }

    private function stream(string $objectKey, string $mimeType, string $name): StreamedResponse
    {
        abort_unless($this->media->objectExists($objectKey), 404);
        $stream = $this->media->readStream($objectKey);
        abort_unless(is_resource($stream), 404);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => HeaderUtils::makeDisposition('inline', $name),
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
