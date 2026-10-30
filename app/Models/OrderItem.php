<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class OrderItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percent' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function subtotal(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    /** Bought at a promotion price (recorded at checkout). */
    public function isPromotional(): bool
    {
        return $this->discount_percent !== null;
    }

    /** Amount knocked off this line by the promotion. */
    public function savings(): float
    {
        return $this->isPromotional() ? ((float) $this->original_price - (float) $this->unit_price) * $this->quantity : 0.0;
    }
}
