<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Carbon\Carbon;

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

public function fetchWeekly(Request $request)
{
    $user = $request->user();

    // Step 1: Fetch weekly aggregates from DB
    $weeklySales = Transaction::where('user_id', $user->id)
        ->selectRaw('YEARWEEK(created_at, 1) as year_week, SUM(total_amount) as amount')
        ->groupBy('year_week')
        ->orderBy('year_week', 'desc')
        ->get();
    Log::info('WeeklySales raw:', $weeklySales->toArray());

    // Step 2: Map with Carbon and error handling
    $weeklySales = $weeklySales->map(function ($item) use ($user) {
        try {
            $yearWeek = str_pad($item->year_week, 6, "0", STR_PAD_LEFT);
            $year = (int) substr($yearWeek, 0, 4);
            $week = (int) substr($yearWeek, 4, 2);

            // Safe ISO week conversion
            $date = Carbon::now();
            $date->setISODate($year, $week);
            $startDate = $date->startOfWeek();
            $endDate = $date->endOfWeek();

            return [
                'week_start' => $startDate->toDateString(),
                'week_end' => $endDate->toDateString(),
                'user' => $user->name,
                'action' => 'Sale',
                'amount' => $item->amount,
            ];
        } catch (\Exception $e) {
            Log::error('Carbon weekly error', [
                'yearWeek' => $item->year_week,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    })->filter(); // remove null entries

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
            ->groupBy('month')
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
