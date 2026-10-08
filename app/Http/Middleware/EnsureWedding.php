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
 * - User biasa tanpa wedding: user baru dari halaman daftar diarahkan ke wizard
 *   onboarding (/onboarding) sampai data Pernikahan dibuat. Akun lama yang
 *   belum dipasangkan masih bisa membuka halaman Profile sebagai fallback.
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
            if ($isAdminArea || $request->routeIs('dashboard', 'logout')) {
                if ($request->isMethod('GET') && $request->routeIs('admin.master-data.index', 'admin.users.index')) {
                    $request->session()->put('weddingPlanner.lastRoute', $request->getRequestUri());
                }

                return $next($request);
            }

            return redirect()->route('admin.users.index');
        }

        if (! $user->wedding_id) {
            // User baru / belum dipasangkan diarahkan ke wizard onboarding.
            if ($request->routeIs('onboarding.*', 'dashboard', 'admin.index', 'logout')) {
                return $next($request);
            }

            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}
