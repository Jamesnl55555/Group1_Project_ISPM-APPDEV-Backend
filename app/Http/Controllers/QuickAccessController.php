<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Transaction;

class QuickAccessController extends Controller
{
    /**
     * Display the Quick Access page.
     */
    // public function index()
    // {
    //     $products = Product::latest()->take(10)->get();
    //     $transactions = Transaction::latest()->take(10)->get();

    //     return Inertia::render('QuickAccess', [
    //         'products' => $products,
    //         'transactions' => $transactions,
    //     ]);
    // }
}
