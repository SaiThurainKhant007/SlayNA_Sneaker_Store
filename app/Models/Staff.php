<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Staff profile for a users row with role "staff"; managed by an admin. */
#[Guarded(['id'])]
class Staff extends Model
{
    protected $table = 'staff';

    protected function casts(): array
    {
        return [
            'hired_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The admin who manages this staff member. */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function processedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'processed_by', 'user_id');
    }

    public function confirmedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'confirmed_by', 'user_id');
    }
}
