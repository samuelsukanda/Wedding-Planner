<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Checklist;
use App\Models\Budget;
use App\Models\Vendor;
use App\Models\Guest;
use App\Models\VendorPayment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $wedding = Wedding::current();
        if (!$wedding) {
            return redirect()->route('checklists.index');
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
