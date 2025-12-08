<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{

   public function fetchDaily(Request $request)
{
    $user = $request->user();
    $perPage = 10;
    $page = $request->query('page', 1);
    $date = $request->query('date'); 

    try {
        $query = Transaction::where('user_id', $user->id);
        if ($date) {
            $query->whereDate('created_at', $date);
        }
        $query->orderBy('created_at', 'desc');

        $dailySales = $query->paginate($perPage, ['*'], 'page', $page);
        $formatted = $dailySales->map(function ($item) use ($user) {
            return [
                'date' => $item->created_at->toDateString(),
                'user' => $user->name,
                'action' => 'Sale', 
                'amount' => $item->total_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'daily_sales' => $formatted,
            'current_page' => $dailySales->currentPage(),
            'last_page' => $dailySales->lastPage(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'daily_sales' => [],
            'message' => 'Failed to fetch daily sales',
        ], 500);
    }
}public function fetchWeekly(Request $request)
{
    $user = $request->user();
    $perPage = $request->input('per_page', 10);
    $page = $request->query('page', 1);

    $query = Transaction::where('user_id', $user->id);

    $weekStart = $request->query('week_start'); // optional
    $weekEnd = $request->query('week_end');     // optional

    if ($weekStart && $weekEnd) {
        $query->whereBetween('created_at', [$weekStart, $weekEnd]);
    }

    $query->orderByDesc('created_at');

    $weeklySales = $query->paginate($perPage, ['*'], 'page', $page);

    $formatted = $weeklySales->map(function ($item) use ($user) {
        return [
            'date' => $item->created_at->toDateString(),
            'user' => $user->name,
            'action' => 'Sale',
            'amount' => $item->total_amount,
        ];
    });

    return response()->json([
        'success' => true,
        'weekly_sales' => $formatted,
        'current_page' => $weeklySales->currentPage(),
        'last_page' => $weeklySales->lastPage(),
        'per_page' => $weeklySales->perPage(),
        'total' => $weeklySales->total(),
    ]);
}


    public function fetchMonthly(Request $request)
{
    $user = $request->user();
    $month = $request->query('month'); // '01' to '12'
    $year = $request->query('year');   // e.g. '2025'
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);

    $query = Transaction::where('user_id', $user->id);

    if ($month && $year) {
        $query->whereYear('created_at', $year)
              ->whereMonth('created_at', $month);
    }

    $query->orderByDesc('created_at');

    $monthlySales = $query->paginate($perPage, ['*'], 'page', $page);

    $formatted = $monthlySales->map(function ($item) use ($user) {
        return [
            'date' => $item->created_at->toDateString(),
            'user' => $user->name,
            'action' => 'Sale',
            'amount' => $item->total_amount,
        ];
    });

    return response()->json([
        'success' => true,
        'monthly_sales' => $formatted,
        'current_page' => $monthlySales->currentPage(),
        'last_page' => $monthlySales->lastPage(),
        'per_page' => $monthlySales->perPage(),
        'total' => $monthlySales->total(),
    ]);
}
    public function fetchCustom(Request $request)
    {
    $user = $request->user();
    $request->validate([
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);

    $from = $request->input('from');
    $to = $request->input('to');
    $perPage = $request->input('per_page', 10);

    try {
        $transactions = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'transactions' => $transactions->items(),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch transactions.'
        ], 500);
    }
    }

}
