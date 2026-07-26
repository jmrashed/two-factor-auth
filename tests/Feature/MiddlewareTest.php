<?php

namespace Jmrashed\TwoFactorAuth\Tests\Feature;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Jmrashed\TwoFactorAuth\Http\Middleware\ConfirmTwoFactorCode;
use Jmrashed\TwoFactorAuth\Http\Middleware\RequireTwoFactorEnabled;
use Jmrashed\TwoFactorAuth\Tests\Fixtures\User;
use Jmrashed\TwoFactorAuth\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    protected function requestFor(User $user): Request
    {
        $request = Request::create('/protected');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_require_two_factor_enabled_blocks_users_without_2fa(): void
    {
        $user = User::create(['name' => 'Dave', 'email' => 'dave@example.com', 'password' => 'secret']);

        $called = false;
        $response = (new RequireTwoFactorEnabled)->handle($this->requestFor($user), function () use (&$called) {
            $called = true;

            return 'next';
        });

        $this->assertFalse($called, 'The next closure must not run for a user without 2FA enabled.');
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_require_two_factor_enabled_allows_users_with_2fa(): void
    {
        $user = User::create(['name' => 'Erin', 'email' => 'erin@example.com', 'password' => 'secret']);
        $user->createTwoFactorAuth();
        $user->confirmTwoFactorAuth($user->makeTwoFactorCode());

        $response = (new RequireTwoFactorEnabled)->handle($this->requestFor($user), fn () => 'next');

        $this->assertSame('next', $response);
    }

    public function test_confirm_two_factor_code_blocks_unconfirmed_session(): void
    {
        $user = User::create(['name' => 'Frank', 'email' => 'frank@example.com', 'password' => 'secret']);
        $user->createTwoFactorAuth();
        $user->confirmTwoFactorAuth($user->makeTwoFactorCode());

        $request = $this->requestFor($user);
        $request->setLaravelSession($this->app['session.store']);

        $called = false;
        (new ConfirmTwoFactorCode)->handle($request, function () use (&$called) {
            $called = true;

            return 'next';
        });

        $this->assertFalse($called, 'The next closure must not run when the 2FA confirmation has not happened yet.');
    }

    public function test_confirm_two_factor_code_allows_when_recently_confirmed(): void
    {
        $user = User::create(['name' => 'Grace', 'email' => 'grace@example.com', 'password' => 'secret']);
        $user->createTwoFactorAuth();
        $user->confirmTwoFactorAuth($user->makeTwoFactorCode());

        $request = $this->requestFor($user);
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put(config('two-factor.confirm.key').'.confirm.expires_at', now()->addHour()->getTimestamp());

        $response = (new ConfirmTwoFactorCode)->handle($request, fn () => 'next');

        $this->assertSame('next', $response);
    }
}
