<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menangani akun yang belum punya data pernikahan.
 *
 * - Superadmin tanpa wedding: hanya boleh di area admin (menu Admin Panel &
 *   Master Data). Semua modul aplikasi di-redirect ke /admin/users supaya
 *   tidak error 500, karena Wedding::current() akan null.
 * - User biasa tanpa wedding: diarahkan ke halaman Profile yang menampilkan
 *   empty state "Akun belum dipasangkan".
 */
class EnsureWedding
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $isAdminArea = $request->routeIs('admin.*');

        if ($user->is_superadmin && ! $user->wedding_id) {
            if ($isAdminArea || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('admin.users.index');
        }

        if (! $user->wedding_id) {
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