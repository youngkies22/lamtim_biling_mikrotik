<?php


namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lamtim_mikrotik;
use App\Models\Lamtim_tagihans;
use App\Models\Lamtim_paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomePage extends Controller
{
  public function index()
  {
    // Get current month and year
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    // Statistics for dashboard
    $stats = [
      // Total Users
      'total_users' => User::where('isActive', 1)->where('idRole', 5)->count(),

      // Active Mikrotik Servers
      'active_mikrotik' => Lamtim_mikrotik::where('isActive', 1)->count(),
      'total_mikrotik' => Lamtim_mikrotik::count(),

      // Monthly Bills Statistics
      'monthly_bills' => Lamtim_tagihans::where('bulan', $currentMonth)
        ->where('tahun', $currentYear)
        ->count(),

      'paid_bills' => Lamtim_tagihans::where('bulan', $currentMonth)
        ->where('tahun', $currentYear)
        ->where('statusBayar', 1)
        ->count(),

      'unpaid_bills' => Lamtim_tagihans::where('bulan', $currentMonth)
        ->where('tahun', $currentYear)
        ->where('statusBayar', 0)
        ->count(),

      // Revenue Statistics
      'monthly_revenue' => Lamtim_tagihans::where('bulan', $currentMonth)
        ->where('tahun', $currentYear)
        ->where('statusBayar', 1)
        ->sum('total'),

      'pending_revenue' => Lamtim_tagihans::where('bulan', $currentMonth)
        ->where('tahun', $currentYear)
        ->where('statusBayar', 0)
        ->sum('total'),

      // Total Packages
      'total_packages' => Lamtim_paket::count(),
    ];

    // Recent transactions (last 5)
    $recent_transactions = Lamtim_tagihans::with(['user', 'paket'])
      ->where('statusBayar', 1)
      ->orderBy('tglBayar', 'desc')
      ->limit(5)
      ->get();

    // Monthly revenue chart data (last 6 months)
    $monthlyRevenue = [];
    for ($i = 5; $i >= 0; $i--) {
      $date = Carbon::now()->subMonths($i);
      $revenue = Lamtim_tagihans::where('bulan', $date->month)
        ->where('tahun', $date->year)
        ->where('statusBayar', 1)
        ->sum('total');
      $monthlyRevenue[] = [
        'month' => $date->format('M Y'),
        'revenue' => $revenue
      ];
    }

    return view('content.pages.home', compact('stats', 'recent_transactions', 'monthlyRevenue'));
  }
}
