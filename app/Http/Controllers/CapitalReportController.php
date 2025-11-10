<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CapitalReportController extends Controller
{
    public function index()
    {
        return Inertia::render('CapitalReports/GenerateCapitalReport');
    }

    public function daily()
    {
        $transactions = Transaction::where('type', 'capital')->whereDate('created_at', today())->get();
        return Inertia::render('CapitalReports/GenerateCapitalReportDaily', [
            'transactions' => $transactions,
        ]);
    }

    public function weekly()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $transactions = Transaction::where('type', 'capital')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();
        return Inertia::render('CapitalReports/GenerateCapitalReportWeekly', [
            'transactions' => $transactions,
        ]);
    }

    public function monthly()
    {
        $transactions = Transaction::where('type', 'capital')
            ->whereMonth('created_at', now()->month)
            ->get();
        return Inertia::render('CapitalReports/GenerateCapitalReportMonthly', [
            'transactions' => $transactions,
        ]);
    }

    public function custom(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $transactions = Transaction::where('type', 'capital')
            ->whereBetween('created_at', [$from, $to])
            ->get();
        return Inertia::render('CapitalReports/GenerateCapitalReportCustom', [
            'transactions' => $transactions,
        ]);
    }
}
