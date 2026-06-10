<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo_path'];

    protected static function booted(): void
    {
        static::created(fn (self $brand) => ContentType::ensureDefaults($brand));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contentPlans(): HasMany
    {
        return $this->hasMany(ContentPlan::class);
    }

    public function contentTypes(): HasMany
    {
        return $this->hasMany(ContentType::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->take(2)
            ->implode('');
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return app(MediaStorageService::class)->displayBrandLogoUrl($this)
            .'?v='.substr(sha1($this->logo_path), 0, 12);
    }

    public static function uniqueSlugFor(User $user, string $name, ?self $ignore = null): string
    {
        $base = Str::slug($name) ?: 'brand';
        $slug = $base;
        $suffix = 2;

        while ($user->brands()
            ->where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
