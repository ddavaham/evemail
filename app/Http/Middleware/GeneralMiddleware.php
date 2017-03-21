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
            $request->session()->flash('alert', [
                'header' => "Unauthorized Page Request",
                'message' => "We apologize for the inconviences, but you must click the button to download your headers before you can browse the rest of the site. The site will break if you don't",
                'type' => 'info',
                'close' => 0
            ]);
            return redirect()->route('dashboard.welcome');
        }

        if ($request->is('welcome') && !Auth::user()->is_new) {
            $request->session()->flash('alert', [
                'header' => "Unauthorized Page Request",
                'message' => "You do not need to visit that page any more.",
                'type' => 'info',
                'close' => 0
            ]);
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
