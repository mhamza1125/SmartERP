<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class All
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->isAdmin() || auth()->user()->role_id !== null) {
            return $next($request);
        }

        // abort(403);
        return redirect()->route('login')->with('fails', 'Login to Access');
    }
}
