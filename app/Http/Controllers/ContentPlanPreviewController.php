<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use App\Services\ContentPlanPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class ContentPlanPreviewController extends Controller
{
    public function show(ContentPlan $contentPlan): View
    {
        $this->authorize('view', $contentPlan);
        $contentPlan->load('brand.contentTypes', 'images');

        return view('contents.preview', compact('contentPlan'));
    }

    public function previewPdf(ContentPlan $contentPlan, ContentPlanPdfService $pdfService): Response
    {
        $this->authorize('view', $contentPlan);

        return $this->pdfResponse($contentPlan, $pdfService, 'inline');
    }

    public function downloadPdf(ContentPlan $contentPlan, ContentPlanPdfService $pdfService): Response
    {
        $this->authorize('view', $contentPlan);

        return $this->pdfResponse($contentPlan, $pdfService, 'attachment');
    }

    public function print(ContentPlan $contentPlan): RedirectResponse
    {
        $this->authorize('view', $contentPlan);

        return redirect()->route('contents.pdf.preview', $contentPlan);
    }

    private function pdfResponse(
        ContentPlan $contentPlan,
        ContentPlanPdfService $pdfService,
        string $disposition,
    ): Response {
        $contentPlan->load('brand.contentTypes', 'images');
        $filename = Str::limit(
            Str::slug($contentPlan->brand->name.'-'.$contentPlan->headline),
            100,
            '',
        ).'-'.$contentPlan->posting_date->format('Y-m-d').'.pdf';

        return response($pdfService->render($contentPlan), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $filename),
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
