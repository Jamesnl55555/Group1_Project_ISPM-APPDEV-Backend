<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Capital;
use Carbon\Carbon;

class CapitalReportController extends Controller
{
    // ================================================================
    // DAILY CAPITAL REPORT
    // ================================================================
    public function fetchDaily(Request $request)
    {
        $user = $request->user();
        $perPage = 10;
        $page = $request->input('page', 1);

        $daily = Capital::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'amount' => $item->total_amount,
                    'action' => 'Capital',   // matches sales structure
                ];
            });

        $total = $daily->count();
        $paged = $daily->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'daily_capital' => $paged,
            'current_page' => (int)$page,
            'last_page'   => (int)ceil($total / $perPage),
        ]);
    }

    // ================================================================
    // WEEKLY CAPITAL REPORT
    // ================================================================
    public function fetchWeekly(Request $request)
    {
        $user = $request->user();
        $perPage = 10;
        $page = $request->input('page', 1);

        $transactions = Capital::where('user_id', $user->id)->get();

        $weekly = $transactions
            ->groupBy(function ($capital) {
                $start = Carbon::parse($capital->created_at)->startOfWeek()->toDateString();
                $end = Carbon::parse($capital->created_at)->endOfWeek()->toDateString();
                return $start . '|' . $end;
            })
            ->map(function ($weekItems, $key) {
                [$start, $end] = explode('|', $key);

                return [
                    'week_start' => $start,
                    'week_end'   => $end,
                    'amount'     => $weekItems->sum('amount'),
                    'action'     => 'Capital',
                ];
            })
            ->sortByDesc('week_start')
            ->values();

        $total = $weekly->count();
        $paged = $weekly->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'weekly_capital' => $paged,
            'current_page' => (int)$page,
            'last_page'   => (int)ceil($total / $perPage),
        ]);
    }

    // ================================================================
    // MONTHLY CAPITAL REPORT
    // ================================================================
    public function fetchMonthly(Request $request)
    {
        $user = $request->user();
        $perPage = 10;
        $page = $request->input('page', 1);

        $monthly = Capital::where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total_amount')
            ->groupBy('year', 'month')
            ->orderByRaw('year DESC, month DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'year'   => $item->year,
                    'month'  => $item->month,
                    'amount' => $item->total_amount,
                    'action' => 'Capital',
                ];
            });

        $total = $monthly->count();
        $paged = $monthly->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'monthly_capital' => $paged,
            'current_page' => (int)$page,
            'last_page'   => (int)ceil($total / $perPage),
        ]);
    }

    // ================================================================
    // CUSTOM RANGE CAPITAL REPORT
    // ================================================================
    public function fetchCustom(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $perPage = 10;
        $page = $request->input('page', 1);

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        $query = Capital::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'date'   => $item->created_at->toDateString(),
                    'amount' => $item->amount,
                    'type'   => $item->type,
                    'action' => 'Capital',
                ];
            });

        $total = $query->count();
        $paged = $query->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'custom_capital' => $paged,
            'current_page' => (int)$page,
            'last_page'   => (int)ceil($total / $perPage),
        ]);
    }
}
