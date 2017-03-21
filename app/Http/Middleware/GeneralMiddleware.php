<?php

namespace EVEMail\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class GeneralMiddleware
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

        if (!$request->is('welcome') && Auth::user()->is_new) {
            return redirect()->route('dashboard.welcome');
        }

        return $next($request);
    }
}
