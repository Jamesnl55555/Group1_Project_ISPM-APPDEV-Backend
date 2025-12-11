<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
class TransactionsController extends Controller
{
    public function fetchTotalAmount(Request $request)
    {
    $user = $request->user();
    $totalAmount = Transaction::where('user_id', $user->id)
        ->sum('total_amount');

    return response()->json([
        'success' => true,
        'user_id' => $user->id,
        'total_amount' => $totalAmount
    ]);
    }

    

    public function fetchLatestThreeTransactions(Request $request)
    {
    $user = $request->user();
    $transactions = Transaction::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get(['total_amount', 'created_at']);

    if ($transactions->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No transactions found.'
        ]);
    }

    $formatted = $transactions->map(function($transaction) {
        return [
            'total_amount' => $transaction->total_amount,
            'time_ago' => Carbon::parse($transaction->created_at)->diffForHumans()
        ];
    });

    return response()->json([
        'success' => true,
        'transactions' => $formatted,
    ]);
    }   

}
