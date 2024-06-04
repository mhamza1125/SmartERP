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
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (auth()->user()->role == 'admin' || auth()->user()->role == 'anyOther') {
            return $next($request);
        }
        // abort(403);
        return redirect()->route('login')->with('fails', 'Login as Admin to Access');
    }
}
