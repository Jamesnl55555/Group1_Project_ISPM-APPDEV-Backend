<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaction;

class ReportsController extends Controller
{
    /**
     * Display the Sales Report page.
     */
    // public function salesReport()
    // {
    //     // Fetch latest 10 transactions for display
    //     $transactions = Transaction::latest()->take(10)->get();

    //     return Inertia::render('Reports/SalesReport', [
    //         'transactions' => $transactions,
    //     ]);
    // }

    // /**
    //  * Generate a detailed Sales Report.
    //  */
    // public function generateSalesReport()
    // {
    //     // Fetch all transactions, adjust query if needed
    //     $transactions = Transaction::latest()->get();

    //     return Inertia::render('Reports/GenerateSalesReport', [
    //         'transactions' => $transactions,
    //     ]);
    // }

    // /**
    //  * Generate a Capital Report.
    //  */
    // public function generateCapitalReport()
    // {
    //     // Fetch all transactions or capital-related data
    //     // You can adjust this query to sum or filter capital-specific transactions
    //     $transactions = Transaction::latest()->get();

    //     return Inertia::render('Reports/GenerateCapitalReport', [
    //         'transactions' => $transactions,
    //     ]);
    // }
}
