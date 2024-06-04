<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;

class AdminController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository){
        $this->middleware(['auth', 'all']);        
        $this->userRepository = $userRepository;
    }
    
    public function index(){
        return view('dashboard');
    }

    public function user(){
        $user = $this->userRepository->all();
        return view('user', [
            'user' => $user,
        ]);
    }
}
