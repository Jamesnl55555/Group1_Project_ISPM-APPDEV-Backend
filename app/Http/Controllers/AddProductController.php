<?php

namespace App\Http\Controllers;
use App\Models\Product;

class AddProductController extends Controller
{
    public function fetchProducts()
    {
        $products = Product::latest()->take(10)->get();

        return response()->json($products);
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }
}
