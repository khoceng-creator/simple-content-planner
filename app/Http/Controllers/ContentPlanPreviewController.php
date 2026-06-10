<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use Illuminate\View\View;

class ContentPlanPreviewController extends Controller
{
    public function show(ContentPlan $contentPlan): View
    {
        $this->authorize('view', $contentPlan);
        $contentPlan->load('brand.contentTypes', 'images');

        return view('contents.preview', compact('contentPlan'));
    }

    public function print(ContentPlan $contentPlan): View
    {
        $this->authorize('view', $contentPlan);
        $contentPlan->load('brand.contentTypes', 'images');

        return view('contents.print', compact('contentPlan'));
    }
}
