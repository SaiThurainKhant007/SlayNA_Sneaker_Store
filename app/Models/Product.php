<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Guarded(['id'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /** Extra photos, in display order (the main photo is image_path). */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Main photo first, then the extra photos: [url, ...]. */
    public function galleryUrls(): array
    {
        return collect([$this->imageUrl()])->merge($this->images->map->imageUrl())->all();
    }

    /** Main photo first, then the extra photos, each with its colour/type name: [['url' => ..., 'label' => ?string], ...]. */
    public function gallery(): array
    {
        return collect([['url' => $this->imageUrl(), 'label' => $this->image_label]])
            ->merge($this->images->map(fn ($photo) => ['url' => $photo->imageUrl(), 'label' => $photo->label]))
            ->all();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Discount Hot Deals are home-page only and no longer attach to shop products, so this
     * relation stays empty. Kept so views/checkout that still eager-load it keep working.
     */
    public function livePromotion(): HasOne
    {
        return $this->hasOne(Promotion::class)->live()->ofType(Promotion::TYPE_PROMOTION)->orderByDesc('discount_percent');
    }

    /** Catalogue / cart price — always the product's own price (Hot Deals do not discount the shop). */
    public function effectivePrice(): float
    {
        return (float) $this->price;
    }

    /** Active products with at least one size at or below the product's low-stock threshold. */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('variants', fn ($q) => $q->whereColumn('product_sizes.stock_quantity', '<=', 'products.low_stock_threshold'));
    }

    public function totalStock(): int
    {
        return (int) $this->variants->sum('stock_quantity');
    }

    public function averageRating(): float
    {
        return round((float) $this->reviews->avg('rating'), 1) ?: 0.0;
    }

    public function reviewCount(): int
    {
        return $this->reviews->count();
    }

    public function formattedPrice(): string
    {
        return number_format($this->effectivePrice(), 0).' MMK';
    }

    public function imageUrl(): string
    {
        if (! $this->image_path || $this->image_path === 'img/no-image.png') {
            return asset('img/no-image.png');
        }

        return asset('storage/'.$this->image_path);
    }
}
