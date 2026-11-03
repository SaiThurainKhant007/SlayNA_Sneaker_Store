<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Staff;
use Closure;

/** Lookups across the three account tables (admins, staff, customers). */
class Accounts
{
    public const MODELS = [Admin::class, Staff::class, Customer::class];

    public static function findByEmail(string $email): Admin|Staff|Customer|null
    {
        foreach (self::MODELS as $model) {
            if ($account = $model::where('email', $email)->first()) {
                return $account;
            }
        }

        return null;
    }

    /** Validation rule: the email is not used by any admin, staff member or customer. */
    public static function uniqueEmail(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (self::findByEmail((string) $value)) {
                $fail('This email is already registered.');
            }
        };
    }
}
