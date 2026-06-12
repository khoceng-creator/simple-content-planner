<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ContentImage;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MediaController extends Controller
{
    public function __construct(private readonly MediaStorageService $media) {}

    public function show(Request $request, ContentImage $contentImage): Response
    {
        $contentImage->load('contentPlan.brand');
        $this->authorize('view', $contentImage->contentPlan);

        return $this->stream(
            $contentImage->file_path,
            $contentImage->mime_type,
            $contentImage->original_name,
            $request,
        );
    }

    public function brandLogo(Request $request, Brand $brand): Response
    {
        $this->authorize('view', $brand);
        abort_unless($brand->logo_path, 404);

        return $this->stream(
            $brand->logo_path,
            $this->media->mimeTypeFromPath($brand->logo_path),
            $brand->slug.'-logo',
            $request,
        );
    }

    private function stream(
        string $objectKey,
        string $mimeType,
        string $name,
        Request $request,
    ): Response {
        $response = new StreamedResponse(status: 200, headers: [
            'Content-Type' => $mimeType,
            'Content-Disposition' => HeaderUtils::makeDisposition('inline', $name),
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->setMaxAge((int) config('media.browser_cache_seconds', 31536000));
        $response->setImmutable();
        $response->setEtag(sha1($objectKey));

        if ($response->isNotModified($request)) {
            return $response;
        }

        try {
            $stream = $this->media->readStream($objectKey);
        } catch (Throwable) {
            abort(404);
        }

        abort_unless(is_resource($stream), 404);
        $response->setCallback(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }
}
