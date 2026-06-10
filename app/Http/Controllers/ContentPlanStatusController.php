<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContentPlanStatusController extends Controller
{
    public function __invoke(Request $request, ContentPlan $contentPlan): JsonResponse|RedirectResponse
    {
        $this->authorize('toggleStatus', $contentPlan);
        $contentPlan->update(['is_made' => ! $contentPlan->is_made]);

        if ($request->expectsJson()) {
            return response()->json(['is_made' => $contentPlan->is_made]);
        }

        return back()->with('success', $contentPlan->is_made
            ? 'Konten ditandai sudah dibuat.'
            : 'Status konten dikembalikan ke belum dibuat.');
    }
}
