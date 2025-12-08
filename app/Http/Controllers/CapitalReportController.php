<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Capital;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CapitalReportController extends Controller
{
    // ================================================================
    // DAILY CAPITAL REPORT
    // ================================================================
    public function fetchDaily(Request $request)
    {
        $user = $request->user();
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required',
            ], 400);
        }

        $perPage = 10;
        $daily = Capital::where('user_id', $user->id)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'daily_capital' => $daily->items(),
            'current_page' => $daily->currentPage(),
            'last_page' => $daily->lastPage(),
        ]);
    }

    // ================================================================
    // WEEKLY CAPITAL REPORT
    // ================================================================
    public function fetchWeekly(Request $request)
    {
        $user = $request->user();
        $weekNumber = $request->input('week');   // 1,2,3...
        $month = $request->input('month');       // "01"-"12"
        $year = $request->input('year');         // e.g., 2025

        if (!$weekNumber || !$month || !$year) {
            return response()->json([
                'success' => false,
                'message' => 'Week, Month, and Year are required',
            ], 400);
        }

        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);
        $startDay = ($weekNumber - 1) * 7 + 1;
        $endDay = min($weekNumber * 7, $firstDayOfMonth->copy()->endOfMonth()->day);

        $weekStart = Carbon::createFromDate($year, $month, $startDay)->startOfDay();
        $weekEnd = Carbon::createFromDate($year, $month, $endDay)->endOfDay();

        $weekly = Capital::where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->selectRaw("
                MIN(DATE(created_at)) as week_start,
                MAX(DATE(created_at)) as week_end,
                SUM(amount) as total_amount
            ")
            ->groupBy(DB::raw("WEEK(created_at, 1)"))
            ->orderBy('week_start', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'weekly_capital' => $weekly->items(),
            'current_page' => $weekly->currentPage(),
            'last_page' => $weekly->lastPage(),
        ]);
    }

    // ================================================================
    // MONTHLY CAPITAL REPORT
    // ================================================================
    public function fetchMonthly(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month'); // "01" to "12"
        $year = $request->input('year');   // e.g., "2025"

        if (!$month || !$year) {
            return response()->json([
                'success' => false,
                'message' => 'Month and Year are required',
            ], 400);
        }

        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $monthly = Capital::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw("
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(amount) as total_amount
            ")
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'monthly_capital' => $monthly->items(),
            'current_page' => $monthly->currentPage(),
            'last_page' => $monthly->lastPage(),
        ]);
    }

    // ================================================================
    // CUSTOM RANGE CAPITAL REPORT
    // ================================================================
    public function fetchCustom(Request $request)
    {
        $user = $request->user();
        $start = $request->input('start');
        $end = $request->input('end');

        if (!$start || !$end) {
            return response()->json([
                'success' => false,
                'message' => 'Start and end dates are required.',
            ], 400);
        }

        $custom = Capital::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'custom_capital' => $custom->items(),
            'current_page' => $custom->currentPage(),
            'last_page' => $custom->lastPage(),
        ]);
    }
}
