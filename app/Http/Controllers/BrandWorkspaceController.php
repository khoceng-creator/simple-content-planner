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
        $defaultPeriod = $request->hasAny(['year', 'month'])
            ? $now
            : $this->defaultPeriod($brand, $now);
        $year = min(max($request->integer('year', $defaultPeriod->year), 2000), 2100);
        $month = min(max($request->integer('month', $defaultPeriod->month), 1), 12);
        $contentTypes = $brand->contentTypes()->get();
        $availableTypes = $contentTypes->pluck('slug')->prepend('semua');
        $type = $availableTypes->containsStrict($request->query('type'))
            ? $request->query('type')
            : 'semua';
        $view = in_array($request->query('view'), ['timeline', 'feed'], true)
            ? $request->query('view')
            : 'timeline';
        $status = in_array($request->query('status'), ['semua', 'dibuat', 'belum'], true)
            ? $request->query('status')
            : 'semua';

        $monthPlans = $brand->contentPlans()
            ->forMonth($year, $month)
            ->with('images')
            ->get();

        $plans = $monthPlans
            ->when($type !== 'semua', fn ($items) => $items->where('type', $type))
            ->when($status === 'dibuat', fn ($items) => $items->where('is_made', true))
            ->when($status === 'belum', fn ($items) => $items->where('is_made', false))
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
                'slug' => $contentType->slug,
                'count' => $monthPlans->where('type', $contentType->slug)->count(),
            ]),
        ];
        $contentTypeLabels = $contentTypes->pluck('name', 'slug');

        $selectedMonth = CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $plansByDay = $monthPlans
            ->sortBy([['posting_date', 'asc'], ['posting_time', 'asc']])
            ->groupBy(fn (ContentPlan $plan) => $plan->posting_date->day);
        $calendarDays = collect(range(1, $selectedMonth->daysInMonth))->map(function (int $day) use ($plansByDay, $now, $year, $month) {
            return [
                'day' => $day,
                'plans' => $plansByDay->get($day, collect())->values(),
                'is_today' => $now->year === $year && $now->month === $month && $now->day === $day,
            ];
        });

        $upcomingNotices = $brand->contentPlans()
            ->whereDate('posting_date', '>=', $now->toDateString())
            ->orderBy('posting_date')
            ->orderBy('posting_time')
            ->limit(5)
            ->get()
            ->map(fn (ContentPlan $plan) => [
                'plan' => $plan,
                'message' => $this->upcomingNotice($plan, $now),
                'date' => $plan->posting_date->locale('id')->translatedFormat('d M Y'),
            ]);

        return view('workspace.show', compact(
            'brand',
            'plans',
            'stats',
            'calendarDays',
            'upcomingNotices',
            'selectedMonth',
            'year',
            'month',
            'type',
            'status',
            'view',
            'contentTypes',
            'contentTypeLabels',
        ));
    }

    private function defaultPeriod(Brand $brand, CarbonImmutable $today): CarbonImmutable
    {
        $postingDate = $brand->contentPlans()
            ->whereDate('posting_date', '>=', $today->toDateString())
            ->orderBy('posting_date')
            ->value('posting_date')
            ?? $brand->contentPlans()->latest('posting_date')->value('posting_date');

        return $postingDate
            ? CarbonImmutable::parse($postingDate, 'Asia/Jakarta')
            : $today;
    }

    private function upcomingNotice(ContentPlan $plan, CarbonImmutable $today): string
    {
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
