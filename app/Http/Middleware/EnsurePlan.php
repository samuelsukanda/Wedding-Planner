<?php

namespace App\Http\Middleware;

use App\Models\Wedding;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan akses ke fitur berbayar dan mengarahkan ke halaman upgrade.
 *
 * Pemakaian: ->middleware('plan:vendor_directory')
 *
 * Paket yang dibutuhkan dibaca dari config('plans.features'), jadi menambah
 * fitur baru cukup mengubah routes/web.php — file ini tidak pernah disentuh.
 *
 * Arahkan ke halaman upgrade tanpa parameter fitur: halaman itu sudah
 * mencantumkan seluruh benefit Pro, jadi tidak perlu konteks tambahan.
 */
class EnsurePlan
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $wedding = Wedding::current();

        if ($wedding && $wedding->hasFeature($feature)) {
            return $next($request);
        }

        return redirect()->route('upgrade.index');
    }
}