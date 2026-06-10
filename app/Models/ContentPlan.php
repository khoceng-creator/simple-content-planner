<?php

namespace App\Models;

use Database\Factories\ContentPlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ContentPlan extends Model
{
    /** @use HasFactory<ContentPlanFactory> */
    use HasFactory;

    public const TYPES = ['carousel', 'reels', 'single'];

    protected $fillable = [
        'posting_date',
        'posting_time',
        'type',
        'platforms',
        'headline',
        'detail_html',
        'note_html',
        'document_link',
        'is_made',
    ];

    protected $appends = ['type_label', 'formatted_schedule', 'image_count'];

    protected function casts(): array
    {
        return [
            'posting_date' => 'date',
            'platforms' => 'array',
            'is_made' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ContentImage::class)->orderBy('sort_order');
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-d', strtotime($start.' +1 month'));

        return $query->whereDate('posting_date', '>=', $start)
            ->whereDate('posting_date', '<', $end);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type && $type !== 'semua', fn (Builder $builder) => $builder->where('type', $type));
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(function (): string {
            $catalogType = $this->relationLoaded('brand') && $this->brand->relationLoaded('contentTypes')
                ? $this->brand->contentTypes->firstWhere('slug', $this->type)
                : null;

            return $catalogType?->name ?? match ($this->type) {
                'carousel' => 'Carousel',
                'reels' => 'Reels',
                'single' => 'Single post',
                default => Str::headline($this->type),
            };
        });
    }

    protected function formattedSchedule(): Attribute
    {
        return Attribute::get(function (): string {
            $date = $this->posting_date?->locale('id')->translatedFormat('D, d F Y') ?? '-';

            return trim($date.($this->posting_time ? ' · '.substr((string) $this->posting_time, 0, 5) : ''));
        });
    }

    protected function imageCount(): Attribute
    {
        return Attribute::get(fn () => $this->relationLoaded('images') ? $this->images->count() : $this->images()->count());
    }
}
