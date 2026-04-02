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

        $salesTrend = Transaction::whereIn('type', ['sell', 'repair'])
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('sum(total_amount) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::parse($row->day)->format('d M'),
                'value' => (float) $row->total,
            ])->values();

        $typeBreakdown = Transaction::select('type', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($row) => [
                'label' => ucfirst($row->type),
                'value' => (int) $row->count,
            ])->values();

        $paymentMix = Transaction::select('payment_method', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->payment_method,
                'value' => (int) $row->count,
            ])->values();

        $outstandingBalance = round((float) Transaction::selectRaw('SUM(CASE WHEN total_amount - COALESCE(amount_paid, total_amount) > 0 THEN total_amount - COALESCE(amount_paid, total_amount) ELSE 0 END) as balance')
            ->value('balance'), 2);

        return Inertia::render('Dashboard', [
            'dailySales' => $dailySales,
            'weeklySales' => $weeklySales,
            'monthlySales' => $monthlySales,
            'transactionCount' => Transaction::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'averageTicket' => (float) Transaction::whereIn('type', ['sell', 'repair'])->where('created_at', '>=', Carbon::now()->subDays(30))->avg('total_amount'),
            'outstandingBalance' => $outstandingBalance,
            'salesTrend' => $salesTrend,
            'typeBreakdown' => $typeBreakdown,
            'paymentMix' => $paymentMix,
            'recentTransactions' => $recentTransactions->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'total_amount' => (float) $transaction->total_amount,
                'receipt_number' => $transaction->receipt_number,
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

        $topCategories = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(transaction_items.quantity) as quantity'))
            ->groupBy('categories.name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $paymentMethods = Transaction::select(
            DB::raw("COALESCE(payment_method, 'Unspecified') as payment_method"),
            DB::raw('count(*) as count'),
            DB::raw('sum(amount_paid) as total_paid')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get();

        $deviceBreakdown = DB::table('transaction_items')
            ->select('brand', 'model', 'description', DB::raw('count(*) as count'))
            ->groupBy('brand', 'model', 'description')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'label' => trim(implode(' ', array_filter([$row->brand, $row->model]))) ?: ($row->description ?: 'Unknown device'),
                'value' => (int) $row->count,
            ])->values();

        $outstandingBalance = round((float) Transaction::selectRaw('SUM(CASE WHEN total_amount - COALESCE(amount_paid, total_amount) > 0 THEN total_amount - COALESCE(amount_paid, total_amount) ELSE 0 END) as balance')
            ->value('balance'), 2);

        return Inertia::render('Reports', [
            'summary' => [
                'sales' => (float) $monthlyData->sum('total'),
                'profit' => (float) $profitData->sum('profit'),
                'repairs' => Transaction::where('type', 'repair')->where('created_at', '>=', Carbon::now()->subMonths(6))->count(),
                'outstanding_balance' => $outstandingBalance,
            ],
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
            'topCategories' => $topCategories->map(fn ($row) => [
                'label' => $row->name,
                'value' => (int) $row->quantity,
            ])->values(),
            'paymentMethods' => $paymentMethods->map(fn ($row) => [
                'label' => $row->payment_method,
                'value' => (int) $row->count,
                'total_paid' => (float) $row->total_paid,
            ])->values(),
            'deviceBreakdown' => $deviceBreakdown,
        ]);
    }
}
