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

    // Get current page from query string
    $perPage = 10;
    $page = $request->query('page', 1);

    try {
        // Query daily sales grouped by date
        $query = Transaction::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'desc');

        // Paginate results
        $dailySales = $query->paginate($perPage, ['*'], 'page', $page);

        // Format the paginated results
        $formatted = $dailySales->getCollection()->map(function ($item) use ($user) {
            return [
                'date' => $item->date,
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
        Log::error('Error fetching daily sales', ['error' => $e->getMessage()]);

        return response()->json([
            'success' => false,
            'daily_sales' => [],
            'message' => 'Failed to fetch daily sales',
        ], 500);
    }
    }


    
    public function fetchWeekly(Request $request)
    {
    $user = $request->user();
    $perPage = 10; 
    $page = $request->input('page', 1);

    $transactions = Transaction::where('user_id', $user->id)->get();

    $weekly_sales = $transactions
        ->groupBy(function ($transaction) {
            $weekStart = Carbon::parse($transaction->created_at)->startOfWeek()->toDateString();
            $weekEnd = Carbon::parse($transaction->created_at)->endOfWeek()->toDateString();
            return $weekStart . '|' . $weekEnd;
        })
        ->map(function ($weekTransactions, $key) use ($user) {
            [$weekStart, $weekEnd] = explode('|', $key);
            $totalAmount = $weekTransactions->sum('total_amount');

            return [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'user' => $user->name,
                'amount' => $totalAmount,
            ];
        })
        ->sortByDesc('week_start')
        ->values(); 

    $totalWeeks = $weekly_sales->count();
    $paginatedWeeks = $weekly_sales->slice(($page - 1) * $perPage, $perPage)->values();

    return response()->json([
        'success' => true,
        'weekly_sales' => $paginatedWeeks,
        'current_page' => (int)$page,
        'last_page' => (int)ceil($totalWeeks / $perPage),
    ]);
    }

    public function fetchMonthly(Request $request)
{
    $user = $request->user();
    $perPage = 10; // items per page
    $page = $request->input('page', 1);

    try {
        $query = Transaction::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw("COALESCE(SUM(total_amount), 0) as amount")
            )
            ->where('user_id', $user->id)
            ->groupBy('month')
            ->orderBy('month', 'asc');

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $monthly_sales = $paginated->getCollection()->map(function ($item) use ($user) {
            return [
                'month' => $item->month,
                'user' => $user->name,
                'amount' => (float) $item->amount,
            ];
        });

        return response()->json([
            'success' => true,
            'monthly_sales' => $monthly_sales,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching monthly sales', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'monthly_sales' => [],
            'message' => 'Failed to fetch monthly sales'
        ], 500);
    }
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

    try {
        $totalAmount = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('total_amount');

        if ($totalAmount == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No sales records found for the selected date range.',
            ]);
        }

        return response()->json([
            'success' => true,
            'custom_sales' => [
                'user' => $user->name,
                'from' => $from,
                'to' => $to,
                'action' => 'Sale', // you can change this if needed
                'amount' => $totalAmount,
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching custom sales report', [
            'user_id' => $user->id,
            'from' => $from,
            'to' => $to,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch custom sales report.',
        ], 500);
    }
    }

}
