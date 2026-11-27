<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class Promotion extends Model
{
    /** Home-page Hot Deals card only — does not change shop catalogue prices. */
    public const TYPE_PROMOTION = 'promotion';

    /** Limited Edition: featured and sold on the home page at full price (never discounted). */
    public const TYPE_LIMITED = 'limited';

    public const TYPES = [self::TYPE_PROMOTION => 'Promotion', self::TYPE_LIMITED => 'Limited Edition'];

    public function isLimited(): bool
    {
        return $this->type === self::TYPE_LIMITED;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Promotion';
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'original_price' => 'decimal:2',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** Brand for display: the promotion's own brand, or the Limited Edition's product brand. */
    public function displayBrand(): ?Brand
    {
        return $this->brand ?? $this->product?->brand;
    }

    /** List / card title under the promo name. */
    public function displayName(): string
    {
        return $this->isLimited() ? (string) $this->product?->name : (string) $this->title;
    }

    /** Full (pre-discount) price shown on the card. */
    public function listPrice(): float
    {
        if ($this->isLimited()) {
            return (float) $this->product->price;
        }

        return (float) $this->original_price;
    }

    /** Today's date in the shop's own timezone, so promotions end at local midnight. */
    public static function today(): string
    {
        return today(config('app.store_timezone'))->toDateString();
    }

    /**
     * A promotion applies from the moment it is switched on until the end of its end date (if any).
     * Discount promotions are home-page cards only; Limited Editions are sold from the home page.
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
        if ($this->isLimited()) {
            return $this->listPrice();
        }

        return round($this->listPrice() * (100 - $this->discount_percent) / 100);
    }

    public function savings(): float
    {
        return $this->listPrice() - $this->discountedPrice();
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return $this->product?->imageUrl() ?? asset('img/no-image.png');
    }

    /** Second image for the hover/tap swap. */
    public function hoverImageUrl(): ?string
    {
        if ($this->hover_image_path) {
            return asset('storage/'.$this->hover_image_path);
        }

        // Limited Edition can fall back to the product photo when only a promo image was set.
        return ($this->image_path && $this->product) ? $this->product->imageUrl() : null;
    }
}
