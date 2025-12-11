<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Capital;
use App\Models\ProductHistory;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function unarchiveItem($id, Request $request)
    {
        $user = $request->user();

        $item = Product::findOrFail($id);
        $item->is_archived = 0;
        $item->save();

        ProductHistory::create([
            'user_id' => $user->id,
            'product_name' => $item->name,
            'action' => 'restored product',
            'changed_data' => "true => false",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product restored successfully.',
        ]);
    }

    public function archiveItem(Request $request, $id)
    {
        $user = $request->user();

        $item = Product::findOrFail($id);
        $item->is_archived = true;
        $item->save();

        ProductHistory::create([
            'user_id' => $user->id,
            'product_name' => $item->name,
            'action' => 'archived product',
            'changed_data' => "false => true",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item archive status updated successfully.',
        ]);
    }

    public function addItem(Request $request)
    {
        $user = $request->user();

        $validatedData = request()->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'category' => 'nullable|string|max:255',
            'is_archived' => 'required|integer',
            'file' => 'required|image',
        ]);

        if ($product = Product::where('name', $validatedData['name'])->first()) {
            return redirect()->back()->withErrors(['name' => 'Product with this name already exists.']);
        }

        $filePath = request()->file('file')->store('images', 'public');
        $product = Product::create([
            'name' => $validatedData['name'],
            'quantity' => $validatedData['quantity'],
            'price' => $validatedData['price'],
            'category' => $validatedData['category'],
            'is_archived' => $validatedData['is_archived'],
            'user_id' => $user->id, // <- Added user_id
            'file_path' => 'storage/' . $filePath,
        ]);

        ProductHistory::create([
            'user_id' => $user->id, // <- Added user_id
            'product_name' => $product->name,
            'action' => 'Added ' . $validatedData['name'],
            'changed_data' => 'none',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully.',
        ]);
    }

    public function checkout(Request $request)
    {
        $validatedData = $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|integer',
            'cart.*.name' => 'required|string',
            'cart.*.quantity' => 'required|integer',
            'cart.*.price' => 'required|numeric',
        ]);

        $user = $request->user();
        $productNumber = Transaction::max('product_number') + 1;
        $cartTotal = 0;
        $varietyOfItems = count($validatedData['cart']);

        foreach ($validatedData['cart'] as $item) {
            $totalAmount = $item['price'] * $item['quantity'];
            $cartTotal += $totalAmount;

            Transaction::create([
                'user_id' => $user->id, // <- Added user_id
                'user_name' => $user->name,
                'product_number' => $productNumber,
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total_amount' => $totalAmount,
                'variety_of_items' => $varietyOfItems,
            ]);

            $product = Product::find($item['id']);
            $product->quantity -= $item['quantity'];
            $product->save();

            ProductHistory::create([
                'user_id' => $user->id, // <- Added user_id
                'product_name' => $item['name'],
                'action' => 'quantity decreased',
                'changed_data' => 'quantity decreased by ' . $item['quantity'],
            ]);
        }

        $capital = Capital::firstOrNew(
            ['created_at' => now()->startOfDay(), 'user_id' => $user->id],
            ['amount' => 0, 'type' => 'income']
        );

        $capital->user_id = $user->id; // <- ensure user_id
        $capital->amount += $cartTotal;
        $capital->type = 'income';
        $capital->save();

        return response()->json([
            'success' => true,
            'message' => 'Checkout completed successfully.',
            'transaction_number' => $productNumber,
        ]);
    }
    public function fetchLatestTransaction(Request $request)
    {
    $user = $request->user();

    // Get the last product_number for this user
    $lastProductNumber = Transaction::where('user_id', $user->id)
        ->max('product_number');

    if (!$lastProductNumber) {
        return response()->json([
            'success' => false,
            'message' => 'No transactions found.'
        ]);
    }

    // Get all transactions with that product_number
    $latestItems = Transaction::where('user_id', $user->id)
        ->where('product_number', $lastProductNumber)
        ->get(['quantity', 'total_amount']);

    // Aggregate totals
    $totalQuantity = $latestItems->sum('quantity');
    $totalAmount = $latestItems->sum('total_amount');

    return response()->json([
        'success' => true,
        'user_name' => $user->name,
        'total_quantity' => $totalQuantity,
        'total_amount' => $totalAmount,
    ]);
    }
    
    public function updateItemInc($id, Request $request)
    {
        $user = $request->user();
        $item = Product::find($id);
        $item->quantity += 1;
        $item->save();

        $user->capital -= $item->price;
        $user->save();

        Transaction::create([
            'user_id' => $user->id, // <- Added user_id
            'user_name' => $user->name,
            'product_name' => $item->name,
            'quantity' => 1,
            'price' => $item->price,
            'total_amount' => $item->price,
        ]);

        ProductHistory::create([
            'user_id' => $user->id, // <- Added user_id
            'product_name' => $item->name,
            'action' => 'quantity increased',
            'changed_data' => 'quantity increased to ' . $item->quantity,
        ]);

        return redirect()->route('dashboard')->with('success', 'Item updated successfully.');
    }

    public function updateItemDec($id, Request $request)
    {
        $user = $request->user();
        $item = Product::find($id);
        $item->quantity -= 1;
        $item->save();

        $user->capital += $item->price;
        $user->save();

        Transaction::create([
            'user_id' => $user->id, // <- Added user_id
            'user_name' => $user->name,
            'product_name' => $item->name,
            'quantity' => -1,
            'price' => $item->price,
            'total_amount' => -$item->price,
        ]);

        ProductHistory::create([
            'user_id' => $user->id, // <- Added user_id
            'product_name' => $item->name,
            'action' => 'quantity decreased',
            'changed_data' => 'quantity decreased to ' . $item->quantity,
        ]);

        return redirect()->route('dashboard')->with('success', 'Item updated successfully.');
    }

    public function updateProduct(Request $request, $id)
    {
        $user = $request->user();
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'category' => 'nullable|string|max:255',
            'is_archived' => 'required|integer',
            'file' => 'nullable|image',
        ]);

        $changedData = [];
        $product = Product::findOrFail($id);

        if ($request->hasFile('file')) {
            if ($product->file_path && Storage::disk('public')->exists(str_replace('storage/', '', $product->file_path))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->file_path));
            }
            $path = $request->file('file')->store('images', 'public');
            $product->file_path = 'storage/' . $path;
            $changedData[] = "Picture updated";
        }

        foreach (['name', 'quantity', 'price', 'category', 'is_archived'] as $field) {
            if ($product->$field != $validatedData[$field]) {
                $changedData[] = ucfirst($field) . " changed from '{$product->$field}' to '{$validatedData[$field]}'";
            }
        }

        $product->fill([
            'name' => $validatedData['name'],
            'quantity' => $validatedData['quantity'],
            'price' => $validatedData['price'],
            'category' => $validatedData['category'],
            'user_id' => $user->id, // <- Added user_id
            'is_archived' => $validatedData['is_archived'],
            'file_path' => $product->file_path,
        ]);

        $product->save();

        if (!empty($changedData)) {
            ProductHistory::create([
                'user_id' => $user->id, // <- Added user_id
                'product_name' => $product->name,
                'action' => 'updated product',
                'changed_data' => implode(', ', $changedData),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
        ]);
    }

    public function deleteItem(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::findOrFail($id);

        ProductHistory::create([
            'user_id' => $user->id, // <- Added user_id
            'product_name' => $product->name,
            'action' => 'deleted product',
            'changed_data' => 'deleted ' . $product->name,
        ]);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function addCapital(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|string'
        ]);

        $validatedData['amount'] = (float) $validatedData['amount'];
        $amount = $user->capital;

        if ($validatedData['type'] == 'add') {
            $newAmount = $amount + $validatedData['amount'];
        } else if ($validatedData['type'] == 'withdraw') {
            $newAmount = $amount - $validatedData['amount'];
        } else {
            $newAmount = $validatedData['amount'];
        }

        $user->update(['capital' => $newAmount]);

        // Save capital record with user_id
        Capital::create([
            'user_id' => $user->id, // <- Added user_id
            'amount' => $validatedData['amount'],
            'type' => $validatedData['type'],
        ]);

        return redirect()->route('dashboard');
    }
}
