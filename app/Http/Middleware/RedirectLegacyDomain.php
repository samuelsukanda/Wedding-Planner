<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectLegacyDomain
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() === 'weddingplanner.web.id') {
            return redirect()->away('https://nupsera.my.id'.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
