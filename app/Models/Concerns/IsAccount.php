<?php

namespace App\Models\Concerns;

/**
 * Shared by the three login tables (admins, staff, customers). Each model sets
 * ROLE, and $user->role keeps working wherever the code needs to know who is signed in.
 */
trait IsAccount
{
    public function getRoleAttribute(): string
    {
        return static::ROLE;
    }

    public function isCustomer(): bool
    {
        return static::ROLE === 'customer';
    }

    public function isStaff(): bool
    {
        return static::ROLE === 'staff';
    }

    public function isAdmin(): bool
    {
        return static::ROLE === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Session guard that signs this kind of account in (config/auth.php). */
    public function guardName(): string
    {
        return static::GUARD;
    }
}
