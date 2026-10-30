<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class ProductVariant extends Model
{
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /** Sizes at or below their product's low-stock threshold (same rule as isLowStock()). */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('product', fn ($q) => $q->whereColumn('product_variants.stock_quantity', '<=', 'products.low_stock_threshold'));
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->product->low_stock_threshold;
    }
}
