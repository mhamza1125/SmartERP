<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * F-10 login throttle, F-28 logout ends the session, F-31 / D10 Reset Test Data only when
 * ALLOW_DATA_RESET is true.
 */
class SecurityBasicsTest extends TestCase
{
    use DatabaseTransactions;

    private function user(): User
    {
        $role = Role::firstOrCreate(['name' => 'Throttle test role']);

        return User::create(['name' => 'Throttle', 'email' => 'throttle-'.uniqid().'@test.local', 'password' => Hash::make('right-pass'), 'role_id' => $role->id]);
    }

    public function test_login_is_throttled_after_five_failures_per_email_and_ip(): void
    {
        $user = $this->user();
        RateLimiter::clear('login|'.strtolower($user->email).'|127.0.0.1');

        foreach (range(1, 5) as $i) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])->assertSessionHas('fails', 'Invalid Login Details');
        }
        // 6th attempt, even with the right password, is refused for now
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'right-pass'])
            ->assertSessionHas('fails', fn ($m) => str_contains($m, 'Too many login attempts'));
        $this->assertGuest();

        // another account from the same IP is not blocked
        $other = $this->user();
        $this->post(route('login.store'), ['email' => $other->email, 'password' => 'right-pass'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($other);
    }

    public function test_successful_login_clears_the_failure_count(): void
    {
        $user = $this->user();
        foreach (range(1, 4) as $i) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong']);
        }
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'right-pass'])->assertRedirect(route('dashboard'));
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])->assertSessionHas('fails', 'Invalid Login Details');
    }

    public function test_logout_invalidates_the_session_and_regenerates_the_token(): void
    {
        $user = $this->user();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'right-pass']);
        $this->assertAuthenticatedAs($user);
        session()->put('marker', 'secret');
        $token = session()->token();
        $sessionId = session()->getId();

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull(session('marker'), 'session data is gone');
        $this->assertNotSame($token, session()->token(), 'CSRF token regenerated');
        $this->assertNotSame($sessionId, session()->getId(), 'new session id');
    }

    public function test_reset_test_data_routes_are_absent_by_default(): void
    {
        $this->assertFalse((bool) config('app.allow_data_reset'));
        $this->assertFalse(Route::has('data-reset.index'));
        $this->assertFalse(Route::has('data-reset.run'));

        $admin = User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();
        $this->actingAs($admin)->get('/settings/reset-data')->assertNotFound();
        $this->actingAs($admin)->post('/settings/reset-data', ['confirmation' => 'RESET'])->assertNotFound();
        // the menu still renders without the link
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertDontSee('Reset Test Data');
    }
}
