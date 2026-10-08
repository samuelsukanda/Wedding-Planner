<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wedding;
use App\Models\Checklist;
use App\Models\Budget;
use App\Models\Vendor;
use App\Models\Guest;
use App\Models\VendorPayment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check()) {
            return view('auth.login');
        }

        $wedding = Wedding::current();
        if (!$wedding) {
            if (auth()->user()?->is_superadmin) {
                $lastRoute = $request->session()->get('weddingPlanner.lastRoute');

                if (str_starts_with($lastRoute ?? '', '/admin/master-data')) {
                    parse_str(parse_url($lastRoute, PHP_URL_QUERY) ?? '', $query);
                    if (isset($query['group'])) {
                        $request->query->set('group', $query['group']);
                    }

                    return app(AdminPanelController::class)->masterData($request);
                }

                return app(AdminPanelController::class)->users();
            }

            return view('onboarding.index');
        }

        // Countdown
        $daysLeft = max(0, (int) Carbon::now()->diffInDays($wedding->wedding_date, false));

        // Checklist Progress
        $totalChecklists = $wedding->checklists()->count();
        $completedChecklists = $wedding->checklists()->where('status', 'Done')->count();
        $uncompletedChecklists = $totalChecklists - $completedChecklists;
        $progressPercent = $totalChecklists > 0 ? round(($completedChecklists / $totalChecklists) * 100) : 0;

        // Budget Summary
        $totalBudget = $wedding->total_budget;
        $budgetTerpakai = $wedding->budgets()->sum('actual_cost');
        $budgetSisa = max(0, $totalBudget - $budgetTerpakai);

        // Quick Statistics
        $totalVendors = $wedding->vendors()->count();
        $totalGuests = $wedding->guests()->sum('guest_count');
        
        $vendorsPaid = VendorPayment::where('wedding_id', $wedding->id)
            ->where('status', 'Lunas')
            ->count();
        $vendorsUnpaid = VendorPayment::where('wedding_id', $wedding->id)
            ->whereIn('status', ['Belum Bayar', 'DP', 'Cicilan'])
            ->count();

        // Recent Notifications & Upcoming Checklists
        $upcomingChecklists = $wedding->checklists()
            ->where('status', '!=', 'Done')
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'wedding',
            'daysLeft',
            'totalChecklists',
            'completedChecklists',
            'uncompletedChecklists',
            'progressPercent',
            'totalBudget',
            'budgetTerpakai',
            'budgetSisa',
            'totalVendors',
            'totalGuests',
            'vendorsPaid',
            'vendorsUnpaid',
            'upcomingChecklists'
        ));
    }
}
