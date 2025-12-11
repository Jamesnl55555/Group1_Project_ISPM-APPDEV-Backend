<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function getUserProducts(Request $request)
    {
        $user = $request->user();
        $latestProducts = Product::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get(['name', 'quantity']);
        

        return response()->json([
            'success' => true,
            'latest_products' => $latestProducts
        ]);
    }
    
    public function countLowStockProducts(Request $request)
    {
        $user = $request->user();

        // Count products with quantity below 20
        $lowStockCount = Product::where('user_id', $user->id)
            ->where('quantity', '<', 20)
            ->count();

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'low_stock_count' => $lowStockCount
        ]);
    }
}
