<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectLegacyDomain
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() === 'weddingplanner.web.id') {
            // GET → 301 (SEO). Non-GET → 307 agar method+body POST dipertahankan,
            // kalau 301 browser akan re-GET dan form kehilangan data.
            $status = $request->isMethod('GET') ? 301 : 307;
            return redirect()->away('https://nupsera.my.id'.$request->getRequestUri(), $status);
        }

        return $next($request);
    }
}
