<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Class diagram: Feedback — a customer's message about the store, sent from Contact Us and read by admins. */
#[Guarded(['id'])]
class Feedback extends Model
{
    protected $table = 'feedback';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
