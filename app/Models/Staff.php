<?php

namespace App\Models;

use App\Models\Concerns\IsAccount;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Class diagram: Staff — managed by an Admin; processes Orders and confirms Payments. */
#[Guarded(['id'])]
#[Hidden(['password', 'remember_token'])]
class Staff extends Authenticatable
{
    use HasFactory, IsAccount;

    public const ROLE = 'staff';

    public const GUARD = 'staff';

    protected $table = 'staff';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'hired_on' => 'date',
        ];
    }

    /** The admin who manages this staff member. */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'managed_by');
    }

    public function processedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'processed_by');
    }

    public function confirmedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'confirmed_by');
    }
}
