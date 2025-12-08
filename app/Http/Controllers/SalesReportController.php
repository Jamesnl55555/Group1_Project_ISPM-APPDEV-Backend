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
        Log::info('Current user', ['user' => $user]);
        $daily_sales = Transaction::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item) use ($user) {
                return [
                    'date' => $item->date,
                    'user' => $user->name,
                    'action' => 'Sale',
                    'amount' => $item->total_amount,
                ];
            });

        return response()->json([
            'success' => true,
            'daily_sales' => $daily_sales,
        ]);
    }

    
    public function fetchWeekly(Request $request){
    $user = $request->user();
    Log::info('Current user', ['user' => $user]);

    // Fetch all transactions for this user
    $transactions = Transaction::where('user_id', $user->id)->get();

    // Group by week start and end
    $weekly_sales = $transactions
        ->groupBy(function ($transaction) {
            // Use Carbon to get the week start (Monday) and week end (Sunday)
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
        ->values(); // reset keys

    return response()->json([
        'success' => true,
        'weekly_sales' => $weekly_sales,
    ]);
    }



    
   public function fetchMonthly(Request $request)
    {
    $user = $request->user(); // authenticated user
    Log::info('Current user', ['user' => $user]);
    $monthly_sales = Transaction::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("user_name as user"),
            DB::raw("SUM(total_amount) as amount")
        )
        ->groupBy('month', 'user')
        ->orderBy('month', 'desc')
        ->get();

    // Format data for frontend
    $monthly_sales = $monthly_sales->map(function ($item) {
        return [
            'month' => $item->month,
            'user' => $item->user,
            'amount' => $item->amount,
        ];
    });

    return response()->json([
        'success' => true,
        'monthly_sales' => $monthly_sales,
    ]);
    }
    public function fetchCustom(Request $request)
    {
        $user = $request->user();
        $from = $request->query('from');
        $to   = $request->query('to');

        if (!$from || !$to) {
            return response()->json([
                'success' => false,
                'message' => 'Both "from" and "to" dates are required.',
            ], 400);
        }

        $totalAmount = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('total_amount');

        return response()->json([
            'success' => true,
            'custom_sales' => [
                'from'   => $from,
                'to'     => $to,
                'user'   => $user->name,
                'action' => 'Sale',
                'amount' => $totalAmount,
            ],
        ]);
    }
}
