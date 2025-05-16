<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! (auth()->attempt($request->only('email', 'password'), $request->remember))) {
            return back()->with('fails', 'Invalid Login Details');
        }

        $role = auth()->user()->pass;

        switch ($role) {
            case 'admin':
                return redirect()->route('dashboard')->with('success', 'Loged In Successfully');
                break;
            default:
                return redirect()->route('dashboard');
                break;
        }
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('login');
    }

    public function register()
    {
        return view('register');
    }

    public function store(UserRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'User Added Successfully');
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User Updated Successfully');

        // $user->update([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'role' => $request->role,
        // ]);

        // if ($request->filled('password')) {
        //     $user->password = Hash::make($request->password);
        //     $user->save();
        // }

    }
}
