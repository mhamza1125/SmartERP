<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function index(){
        return view('login');
    }
    
    public function login(Request $request){   
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(!(auth()->attempt($request->only('email', 'password'), $request->remember))){
            return back()->with('status', 'Invalid Login Details');
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

    public function logout(){
        auth()->logout();
        return redirect()->route('login');
    }

    public function register(){
        return view('register');
    }

    public function store(RegisterRequest $request){
        $data = $request->validated();

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->attempt($request->only('email', 'password'));

        return redirect()->route('login');
    }
}
