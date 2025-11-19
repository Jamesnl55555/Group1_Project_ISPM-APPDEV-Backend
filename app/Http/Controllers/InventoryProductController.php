<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryProductController extends Controller
{
//     /**
//      * Display the add product page
//      */
//     public function create()
//     {
//         return inertia('Inventory/AddProduct');
//     }

//     /**
//      * Store a newly added product
//      */
//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'quantity' => 'required|integer',
//             'price' => 'required|numeric',
//             'category' => 'nullable|string|max:255',
//             'is_archived' => 'required|boolean',
//             'file' => 'required|image',
//         ]);

//         $userId = $request->user()->id;

//         if (Product::where('name', $validated['name'])->exists()) {
//             return redirect()->back()->withErrors(['name' => 'Product with this name already exists.']);
//         }

//         $filePath = $request->file('file')->store('images', 'public');

//         $product = Product::create([
//             'name' => $validated['name'],
//             'quantity' => $validated['quantity'],
//             'price' => $validated['price'],
//             'category' => $validated['category'],
//             'is_archived' => $validated['is_archived'],
//             'user_id' => $userId,
//             'file_path' => 'storage/' . $filePath,
//         ]);

//         ProductHistory::create([
//             'product_name' => $product->name,
//             'action' => 'Added product',
//             'changed_data' => 'Initial creation',
//         ]);

//         return redirect()->route('dashboard')->with('success', 'Product added successfully.');
//     }

// //     /**
// //      * Update an existing product
// //      */
//     public function update(Request $request, $id)
//     {
//         $product = Product::findOrFail($id);

//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'quantity' => 'required|integer',
//             'price' => 'required|numeric',
//             'category' => 'nullable|string|max:255',
//             'is_archived' => 'required|boolean',
//             'file' => 'nullable|image',
//         ]);

//         $changes = [];

//         // Update image if provided
//         if ($request->hasFile('file')) {
//             if ($product->file_path && Storage::disk('public')->exists(str_replace('storage/', '', $product->file_path))) {
//                 Storage::disk('public')->delete(str_replace('storage/', '', $product->file_path));
//             }

//             $path = $request->file('file')->store('images', 'public');
//             $product->file_path = 'storage/' . $path;
//             $changes[] = 'Image updated';
//         }

//         // Detect changes in fields
//         foreach (['name', 'quantity', 'price', 'category', 'is_archived'] as $field) {
//             if ($product->$field != $validated[$field]) {
//                 $changes[] = ucfirst($field) . " changed from '{$product->$field}' to '{$validated[$field]}'";
//             }
//         }

//         $product->update([
//             'name' => $validated['name'],
//             'quantity' => $validated['quantity'],
//             'price' => $validated['price'],
//             'category' => $validated['category'],
//             'is_archived' => $validated['is_archived'],
//         ]);

//         if (!empty($changes)) {
//             ProductHistory::create([
//                 'product_name' => $product->name,
//                 'action' => 'Updated product',
//                 'changed_data' => implode(', ', $changes),
//             ]);
//         }

//         return redirect()->route('dashboard')->with('success', 'Product updated successfully.');
//     }

// //     /**
// //      * Delete a product
// //      */
//     public function destroy($id)
//     {
//         $product = Product::findOrFail($id);

//         ProductHistory::create([
//             'product_name' => $product->name,
//             'action' => 'Deleted product',
//             'changed_data' => 'Deleted ' . $product->name,
//         ]);

//         $product->delete();

//         return redirect()->route('dashboard')->with('success', 'Product deleted successfully.');
//     }
}
