<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class GalleryImage extends Model
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Optional "Shop this look" product. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Visible photos in display order. */
    public function scopeShown(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): string
    {
        return asset('storage/'.$this->image_path);
    }

    /** Text for screen readers and the lightbox. */
    public function altText(): string
    {
        return trim(($this->title ?? '').' '.($this->subtitle ?? '')) ?: 'SlayNa promotional photo';
    }
}
