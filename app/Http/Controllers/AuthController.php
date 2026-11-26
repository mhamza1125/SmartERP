<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        // Creating / editing users needs a signed-in user with the Users permissions
        $this->middleware(['auth', 'all'])->only(['store', 'update']);
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // At most 5 failed attempts per minute for one email from one IP (F-10)
        $throttleKey = 'login|'.Str::lower((string) $request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->with('fails', 'Too many login attempts. Try again in '.RateLimiter::availableIn($throttleKey).' seconds.')
                ->withInput($request->only('email'));
        }

        if (! (auth()->attempt($request->only('email', 'password'), $request->remember))) {
            RateLimiter::hit($throttleKey, 60);

            return back()->with('fails', 'Invalid Login Details');
        }
        RateLimiter::clear($throttleKey);

        if (auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('success', 'Logged In Successfully');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        // End the session itself and issue a new CSRF token (F-28)
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function store(UserRequest $request)
    {
        $this->authorize('users_create', User::class);
        $this->guardAdminRole((int) $request->role_id);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'User Added Successfully');
    }

    public function update(UserRequest $request, $id)
    {
        $this->authorize('users_edit', User::class);
        $user = User::findOrFail($id);
        $this->guardAdminRole((int) $request->role_id, $user);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User Updated Successfully');
    }

    /**
     * Users permissions let a role manage accounts, but never create an administrator or change
     * one: that would let any user manager make themselves Admin (system-level restriction).
     */
    private function guardAdminRole(int $roleId, ?User $target = null): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }
        abort_if($roleId === User::ROLE_ADMIN || ($target && $target->isAdmin()), 403, 'Only an administrator can give the Admin role or change an administrator account.');
    }
}
