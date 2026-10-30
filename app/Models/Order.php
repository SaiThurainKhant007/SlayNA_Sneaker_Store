<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Guarded(['id'])]
class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'];

    /** The normal path an order moves along, shown as a timeline to the customer. */
    public const STEPS = ['pending' => 'Placed', 'confirmed' => 'Confirmed', 'processing' => 'Packing', 'shipped' => 'Shipped', 'completed' => 'Delivered'];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /** True when at least one line was bought at a promotion price. */
    public function hasPromotion(): bool
    {
        return $this->items->contains(fn (OrderItem $item) => $item->isPromotional());
    }

    public function promotionSavings(): float
    {
        return $this->items->sum(fn (OrderItem $item) => $item->savings());
    }

    public function formattedTotal(): string
    {
        return number_format((float) $this->total_amount, 0).' MMK';
    }

    /** Customers may cancel only before staff have confirmed the order. */
    public function canBeCancelledByCustomer(): bool
    {
        return $this->order_status === 'pending';
    }

    /** Index of the current status in STEPS (-1 when cancelled). */
    public function stepIndex(): int
    {
        $index = array_search($this->order_status, array_keys(self::STEPS), true);

        return $index === false ? -1 : $index;
    }

    /**
     * Mark the order cancelled and put its items back in stock.
     * Restocks only on the first cancel, so repeating it cannot inflate stock.
     */
    public function cancel(?int $processedBy = null): void
    {
        DB::transaction(function () use ($processedBy) {
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if ($order->order_status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update(array_filter(['order_status' => 'cancelled', 'processed_by' => $processedBy]));
            $this->setRawAttributes($order->getAttributes(), true);
        });
    }

    public function statusColor(): string
    {
        return match ($this->order_status) {
            'pending' => 'secondary',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
