<?php

namespace App\Models;

use App\Models\Concerns\IsAccount;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Class diagram: Admin — manages Staff, the catalogue, promotions and reports. */
#[Guarded(['id'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    use HasFactory, IsAccount;

    public const ROLE = 'admin';

    public const GUARD = 'admin';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /** Staff this admin manages. */
    public function managedStaff(): HasMany
    {
        return $this->hasMany(Staff::class, 'managed_by');
    }
}
