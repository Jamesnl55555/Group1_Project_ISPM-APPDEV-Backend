<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function fetchDaily(Request $request)
    {
        $user = $request->user();

        $dailySales = Transaction::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount')
            ->groupBy(DB::raw('DATE(created_at)'))
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

        if ($dailySales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No daily sales records found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'daily_sales' => $dailySales,
        ]);
    }

    public function fetchWeekly(Request $request)
    {
        $user = $request->user();

        $weeklySales = Transaction::where('user_id', $user->id)
            ->selectRaw('YEARWEEK(created_at, 1) as year_week, SUM(total_amount) as total_amount')
            ->groupBy(DB::raw('YEARWEEK(created_at, 1)'))
            ->orderBy('year_week', 'desc')
            ->get()
            ->map(function ($item) use ($user) {
                $year = substr($item->year_week, 0, 4);
                $week = substr($item->year_week, 4, 2);
                $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();

                return [
                    'week_start' => $startDate->toDateString(),
                    'week_end'   => $endDate->toDateString(),
                    'user'       => $user->name,
                    'action'     => 'Sale',
                    'amount'     => $item->total_amount,
                ];
            });

        if ($weeklySales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No weekly sales records found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'weekly_sales' => $weeklySales,
        ]);
    }

    public function fetchMonthly(Request $request)
    {
        $user = $request->user();

        $monthlySales = Transaction::where('user_id', $user->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as total_amount')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) use ($user) {
                return [
                    'month' => $item->month,
                    'user' => $user->name,
                    'action' => 'Sale',
                    'amount' => $item->total_amount,
                ];
            });

        if ($monthlySales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No monthly sales records found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'monthly_sales' => $monthlySales,
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

        if ($totalAmount == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No sales found for the selected date range.',
            ], 404);
        }

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
