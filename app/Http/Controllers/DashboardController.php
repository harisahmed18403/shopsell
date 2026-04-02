<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Revenue from both 'sell' and 'repair'
        $dailySales = Transaction::whereIn('type', ['sell', 'repair'])
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $weeklySales = Transaction::whereIn('type', ['sell', 'repair'])
            ->whereBetween('created_at', [$startOfWeek, Carbon::now()])
            ->sum('total_amount');

        $monthlySales = Transaction::whereIn('type', ['sell', 'repair'])
            ->whereBetween('created_at', [$startOfMonth, Carbon::now()])
            ->sum('total_amount');

        $recentTransactions = Transaction::with(['customer', 'items.product'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'dailySales' => $dailySales,
            'weeklySales' => $weeklySales,
            'monthlySales' => $monthlySales,
            'recentTransactions' => $recentTransactions->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'total_amount' => (float) $transaction->total_amount,
                'customer_name' => $transaction->customer?->name ?? 'Walk-in customer',
                'created_at' => $transaction->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function reports(): Response
    {
        // Monthly sales (Sell + Repair)
        $monthlyData = Transaction::whereIn('type', ['sell', 'repair'])
            ->select(
                DB::raw('sum(total_amount) as total'),
                DB::raw("strftime('%Y-%m', created_at) as month")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Buy vs Sell comparison
        $buyVsSell = Transaction::select(
            DB::raw('type'),
            DB::raw('sum(total_amount) as total'),
            DB::raw("strftime('%Y-%m', created_at) as month")
        )
            ->whereIn('type', ['buy', 'sell'])
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get();

        // Profit (Sell + Repair - Buy)
        $profitData = Transaction::select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw("SUM(CASE WHEN type IN ('sell', 'repair') THEN total_amount ELSE 0 END) - SUM(CASE WHEN type = 'buy' THEN total_amount ELSE 0 END) as profit")
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Reports', [
            'monthlyData' => $monthlyData->map(fn ($row) => [
                'month' => $row->month,
                'total' => (float) $row->total,
            ])->values(),
            'buyVsSell' => $buyVsSell->map(fn ($row) => [
                'month' => $row->month,
                'type' => $row->type,
                'total' => (float) $row->total,
            ])->values(),
            'profitData' => $profitData->map(fn ($row) => [
                'month' => $row->month,
                'profit' => (float) $row->profit,
            ])->values(),
        ]);
    }
}
