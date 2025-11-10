<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Transaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a list of transactions.
     */
    public function index()
    {
        $transactions = Transaction::latest()->take(10)->get();
        return response()->json($transactions);
    }

//     /**
//      * Display a specific transaction (Full Transaction Information page).
//      */
//     public function show($id)
//     {
//         // ✅ Normally, you’d fetch from DB using Transaction::find($id)
//         // For now, we’ll use a static example:
//         $transaction = [
//             'id' => $id,
//             'date' => '8/17/2025 - 7:00 AM',
//             'method' => 'Cash',
//             'amount' => 2050,
//             'items' => [
//                 ['category' => '#000020', 'name' => 'Buldak C.', 'price' => 250, 'quantity' => 4],
//                 ['category' => '#000043', 'name' => 'Large Cadbury', 'price' => 500, 'quantity' => 1],
//                 ['category' => '#000007', 'name' => 'Pringles Orig.', 'price' => 200, 'quantity' => 2],
//                 ['category' => '#000020', 'name' => 'Binggrae B. Milk', 'price' => 70, 'quantity' => 3],
//                 ['category' => '#000003', 'name' => 'Lotte Pepero M.', 'price' => 30, 'quantity' => 2],
//             ],
//         ];

//         // ✅ Render the TransactionDetails page under Reports/
//         return Inertia::render('Reports/TransactionDetails', [
//             'transaction' => $transaction,
//         ]);
//     }


//     public function createTransaction()
//     {
//         $products = Product::all();
//         return Inertia::render('AddItem', [
//             'products' => $products,
//         ]);
//     }   
}
