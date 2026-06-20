<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Database\Factories\ContentImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentImage extends Model
{
    /** @use HasFactory<ContentImageFactory> */
    use HasFactory;

    protected $fillable = [
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function contentPlan(): BelongsTo
    {
        return $this->belongsTo(ContentPlan::class);
    }

    public function displayUrl(): string
    {
        return app(MediaStorageService::class)->displayUrl($this);
    }

    public function downloadUrl(): string
    {
        return route('media.show', ['contentImage' => $this, 'download' => 1]);
    }
}
