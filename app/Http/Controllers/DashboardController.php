<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $dailySales = Transaction::where('type', 'sell')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $weeklySales = Transaction::where('type', 'sell')
            ->whereBetween('created_at', [$startOfWeek, Carbon::now()])
            ->sum('total_amount');

        $monthlySales = Transaction::where('type', 'sell')
            ->whereBetween('created_at', [$startOfMonth, Carbon::now()])
            ->sum('total_amount');

        $recentTransactions = Transaction::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::where('quantity', '<', 5)
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'dailySales', 
            'weeklySales', 
            'monthlySales', 
            'recentTransactions',
            'lowStockProducts'
        ));
    }

    public function reports()
    {
        // Monthly sales for the last 6 months
        $monthlyData = Transaction::where('type', 'sell')
            ->select(
                DB::raw('sum(total_amount) as total'),
                DB::raw("strftime('%Y-%m', created_at) as month")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('reports', compact('monthlyData'));
    }
}
