<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContentType extends Model
{
    public const DEFAULTS = [
        ['name' => 'Carousel', 'slug' => 'carousel', 'sort_order' => 10],
        ['name' => 'Reels', 'slug' => 'reels', 'sort_order' => 20],
        ['name' => 'Single post', 'slug' => 'single', 'sort_order' => 30],
    ];

    protected $fillable = [
        'name',
        'slug',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public static function ensureDefaults(Brand $brand): Collection
    {
        return collect(self::DEFAULTS)->map(fn (array $type) => $brand->contentTypes()->firstOrCreate(
            ['slug' => $type['slug']],
            [...$type, 'is_default' => true],
        ));
    }

    public static function resolveFor(Brand $brand, string $slug, ?string $newName = null): self
    {
        if ($slug !== '__new') {
            return $brand->contentTypes()->where('slug', $slug)->firstOrFail();
        }

        $name = Str::squish((string) $newName);
        $customSlug = Str::of($name)->slug()->limit(30, '')->trim('-')->toString() ?: 'tipe-konten';

        return $brand->contentTypes()->firstOrCreate(
            ['slug' => $customSlug],
            ['name' => $name, 'is_default' => false, 'sort_order' => 100],
        );
    }
}
