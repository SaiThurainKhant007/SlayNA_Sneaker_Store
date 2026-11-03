<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Class diagram: PaymentMethod 1 — used for — 0..* Payment (kpay, ayapay, cod, saved_card, bank_transfer). */
#[Guarded(['id'])]
class PaymentMethod extends Model
{
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Id of the method with this code, as posted by the checkout form. */
    public static function idFor(string $code): int
    {
        return static::where('code', $code)->valueOrFail('id');
    }
}
