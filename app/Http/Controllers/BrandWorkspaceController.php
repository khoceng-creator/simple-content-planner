<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ContentPlan;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandWorkspaceController extends Controller
{
    public function show(Request $request, Brand $brand): View
    {
        $this->authorize('view', $brand);

        $now = CarbonImmutable::now('Asia/Jakarta');
        $year = min(max($request->integer('year', $now->year), 2000), 2100);
        $month = min(max($request->integer('month', $now->month), 1), 12);
        $contentTypes = $brand->contentTypes()->get();
        $availableTypes = $contentTypes->pluck('slug')->prepend('semua');
        $type = $availableTypes->containsStrict($request->query('type'))
            ? $request->query('type')
            : 'semua';
        $view = in_array($request->query('view'), ['timeline', 'feed'], true)
            ? $request->query('view')
            : 'timeline';

        $monthPlans = $brand->contentPlans()
            ->forMonth($year, $month)
            ->with('images')
            ->get();

        $plans = $monthPlans
            ->when($type !== 'semua', fn ($items) => $items->where('type', $type))
            ->sortBy([
                ['posting_date', $view === 'timeline' ? 'asc' : 'desc'],
                ['posting_time', $view === 'timeline' ? 'asc' : 'desc'],
            ])
            ->values();

        $stats = [
            'total' => $monthPlans->count(),
            'carousel' => $monthPlans->where('type', 'carousel')->count(),
            'reels' => $monthPlans->where('type', 'reels')->count(),
            'single' => $monthPlans->where('type', 'single')->count(),
            'made' => $monthPlans->where('is_made', true)->count(),
            'remaining' => $monthPlans->where('is_made', false)->count(),
            'types' => $contentTypes->map(fn ($contentType) => [
                'name' => $contentType->name,
                'count' => $monthPlans->where('type', $contentType->slug)->count(),
            ]),
        ];
        $contentTypeLabels = $contentTypes->pluck('name', 'slug');

        $selectedMonth = CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $calendarDays = collect(range(1, $selectedMonth->daysInMonth))->map(fn (int $day) => [
            'day' => $day,
            'has_content' => $monthPlans->contains(fn ($plan) => $plan->posting_date->day === $day),
            'is_today' => $now->year === $year && $now->month === $month && $now->day === $day,
        ]);

        $upcoming = $brand->contentPlans()
            ->whereDate('posting_date', '>=', $now->toDateString())
            ->orderBy('posting_date')
            ->orderBy('posting_time')
            ->first();
        $upcomingNotice = $this->upcomingNotice($upcoming, $now);

        return view('workspace.show', compact(
            'brand',
            'plans',
            'stats',
            'calendarDays',
            'upcomingNotice',
            'selectedMonth',
            'year',
            'month',
            'type',
            'view',
            'contentTypes',
            'contentTypeLabels',
        ));
    }

    private function upcomingNotice(?ContentPlan $plan, CarbonImmutable $today): string
    {
        if (! $plan) {
            return 'Belum ada jadwal konten mendatang.';
        }

        $today = $today->startOfDay();
        $postingDate = CarbonImmutable::instance($plan->posting_date)->setTimezone('Asia/Jakarta')->startOfDay();
        $time = $plan->posting_time
            ? ' pukul '.str_replace(':', '.', substr((string) $plan->posting_time, 0, 5))
            : '';

        if ($postingDate->isSameDay($today)) {
            return "Jadwal terdekat hari ini{$time}.";
        }

        if ($postingDate->isSameDay($today->addDay())) {
            return "Jadwal terdekat besok{$time}.";
        }

        $days = (int) $today->diffInDays($postingDate);

        return "Jadwal terdekat {$days} hari lagi{$time}.";
    }
}
