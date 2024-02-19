<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Operator
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
        if (auth()->user()->pass == 'admin') {
            return $next($request);
        }
        // abort(403);
        return redirect()->route('login')->with('fails', 'Login as Operator to Access');
    }
}
