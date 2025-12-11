<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function amountsOverTime(Request $request)
    {
        $user = $request->user();

        $data = Transaction::where('user_id', $user->id)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
    }
}
