<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class Promotion extends Model
{
    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Today's date in the shop's own timezone, so promotions end at local midnight. */
    public static function today(): string
    {
        return today(config('app.store_timezone'))->toDateString();
    }

    /**
     * A promotion applies from the moment it is switched on until the end of its end date (if any).
     * The same rule drives the home page, product pages, cart and checkout, so the advertised price is always the charged price.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('ends_on')->orWhere('ends_on', '>=', self::today()));
    }

    public function isLive(): bool
    {
        return $this->is_active && (! $this->ends_on || $this->ends_on->toDateString() >= self::today());
    }

    /** Price after discount, rounded to whole kyats. */
    public function discountedPrice(): float
    {
        return round((float) $this->product->price * (100 - $this->discount_percent) / 100);
    }

    public function savings(): float
    {
        return (float) $this->product->price - $this->discountedPrice();
    }

    public function imageUrl(): string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : $this->product->imageUrl();
    }

    /** Second image for the hover/tap swap; the product photo stands in when only a promo image was uploaded. */
    public function hoverImageUrl(): ?string
    {
        if ($this->hover_image_path) {
            return asset('storage/'.$this->hover_image_path);
        }

        return $this->image_path ? $this->product->imageUrl() : null;
    }
}
