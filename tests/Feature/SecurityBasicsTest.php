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
 * F-10 login throttle, F-28 logout ends the session, Reset Test Data visible to the admin only
 * (user decision 2026-11-25; the ALLOW_DATA_RESET switch of D10 is gone).
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

    public function test_reset_test_data_is_available_to_the_admin_only(): void
    {
        $this->assertTrue(Route::has('data-reset.index'));
        $this->assertTrue(Route::has('data-reset.run'));

        // Admin: menu link + the confirmation screen (a preview only; nothing is run here)
        $admin = User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Reset Test Data');
        $this->actingAs($admin)->get(route('data-reset.index'))->assertOk();

        // Any other user: no link, and neither URL is reachable
        $user = $this->user();
        $this->actingAs($user)->get(route('dashboard'))->assertDontSee('Reset Test Data');
        $this->assertNotSame(200, $this->actingAs($user)->get(route('data-reset.index'))->getStatusCode());
        $this->assertNotSame(200, $this->actingAs($user)->post(route('data-reset.run'), ['confirmation' => 'RESET'])->getStatusCode());
    }
}
