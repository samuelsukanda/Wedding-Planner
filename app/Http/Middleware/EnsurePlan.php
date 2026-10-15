<?php

namespace App\Http\Middleware;

use App\Models\Wedding;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan akses ke fitur berbayar dan mengarahkan ke halaman upgrade.
 *
 * Pemakaian: ->middleware('plan:moodboard')
 *
 * Paket yang dibutuhkan dibaca dari config('plans.features'), jadi menambah
 * fitur baru cukup mengubah routes/web.php — file ini tidak pernah disentuh.
 */
class EnsurePlan
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $wedding = Wedding::current();

        if ($wedding && $wedding->hasFeature($feature)) {
            return $next($request);
        }

        return redirect()->route('upgrade.index', ['from' => $feature]);
    }
}