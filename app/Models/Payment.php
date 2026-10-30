<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class Payment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Human-readable payment method, e.g. "KPay" or "Cash on Delivery". */
    public function methodLabel(): string
    {
        return config('payments.wallets.'.$this->payment_method.'.short')
            ?? match ($this->payment_method) {
                'cod' => 'Cash on Delivery',
                'saved_card' => 'Saved Card',
                default => 'Bank Transfer',
            };
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
