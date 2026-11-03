<?php

namespace Tests;

use App\Http\Middleware\UseSignedInGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Admins, staff and customers each sign in through their own guard (config/auth.php). */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        $guard ??= method_exists($user, 'guardName') ? $user->guardName() : null;

        // Like a real login, switching account signs the previous one out
        foreach (UseSignedInGuard::GUARDS as $other) {
            if ($other !== $guard) {
                $this->app['auth']->guard($other)->forgetUser();
            }
        }

        return parent::actingAs($user, $guard);
    }
}
