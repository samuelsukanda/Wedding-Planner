<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang belum dipasangkan dengan data pernikahan tidak punya wedding_id, sehingga
 * halaman modul akan gagal (data null). Arahkan saja ke halaman Profile agar muncul
 * penjelasan, bukan error 500.
 */
class EnsureWedding
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_superadmin && ! $user->wedding_id) {
            if ($request->routeIs('admin.index') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('admin.index')->with(
                'error',
                'Akun ini belum terhubung ke data pernikahan. Hubungi superadmin untuk dipasangkan.'
            );
        }

        return $next($request);
    }
}