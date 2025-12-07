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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class InventoryController extends Controller
{
    public function unarchiveItem($id)
    {
    $item = Product::findOrFail($id);
    $item->is_archived = 0;
    $item->save();

    ProductHistory::create([
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
    $item = Product::findOrFail($id);
    $item->is_archived = true;
    $item->save();

    ProductHistory::create([
        'product_name' => $item->name,
        'action' => 'archived product',
        'changed_data' => "false => true",
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Item archive status updated successfully.',
    ]);
    }


    public function addItem(Request $request){
        $validatedData = request()->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'category' => 'nullable|string|max:255',
            'is_archived' => 'required|integer',
            'file' => 'required|image',
        ]);
        $userid = $request->user()->id;
        if ($product = Product::where('name', $validatedData['name'])->first() ) {
            return redirect()->back()->withErrors(['name' => 'Product with this name already exists.']);
        }
        $filePath = request()->file('file')->store('images', 'public');
        $product = Product::create([
            'name' => $validatedData['name'],
            'quantity' => $validatedData['quantity'],
            'price' => $validatedData['price'],
            'category' => $validatedData['category'],
            'is_archived' => $validatedData['is_archived'],
            'user_id' => $userid,
            'file_path' => 'storage/' . $filePath,
        ]);

        ProductHistory::create([
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

    $productnumber = Transaction::max('id') + 1;
    $user = $request->user();

    foreach ($validatedData['cart'] as $item) {

        Transaction::create([
            'user_name' => $user->name,
            'product_number' => $productnumber,
            'product_name' => $item['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'total_amount' => $item['price'] * $item['quantity']
        ]);

        $product = Product::find($item['id']);
        $product->quantity -= $item['quantity'];
        $product->save();

        ProductHistory::create([
            'product_name' => $item['name'],
            'action' => 'quantity decreased',
            'changed_data' => 'quantity decreased by ' . $item['quantity'],
        ]);
    }

    $cartTotal = array_sum(array_map(function ($item) {
        return $item['price'] * $item['quantity'];
    }, $validatedData['cart']));

    $today = now()->toDateString();

    $capital = Capital::firstOrNew(
        ['created_at' => now()->startOfDay()],
        ['amount' => 0, 'type' => 'income']
    );

    // Add today's checkout total
    $capital->amount += $cartTotal;
    $capital->type = 'income';
    $capital->save();

    return response()->json([
        'success' => true,
        'message' => 'Checkout completed successfully.',
    ]);
    }



    public function updateItemInc($id)
    {
        $validatedData = request()->validate([
            'quantity' => 'sometimes|required|integer',
        ]);
        $item = Product::find($id);
        $i = $validatedData['quantity'] + 1;
        $item->quantity = $i;
        $item->save();

        $user = User::find($item->user_id);
        $user->capital = $user->capital - $item->price;
        $user->save();

        Transaction::create([
            'user_name' => $user->name,
            'product_name' => $item->name,
            'quantity' => 1,
            'price' => $item->price,
            'total_amount' => $item->price,
        ]);

        ProductHistory::create([
            'product_name' => $item->name,
            'action' => 'quantity increased',
            'changed_data' => 'quantity increased to ' . $item->quantity,
        ]);
              
        return redirect()->route('dashboard')->with('success', 'Item updated successfully.');
    }

    public function updateItemDec($id)
    {
        $validatedData = request()->validate([
            'quantity' => 'sometimes|required|integer',
        ]);
        $item = Product::find($id);
        $i = $validatedData['quantity'] - 1;
        $item->quantity = $i;
        $item->save();

        $user = User::find($item->user_id);
        $user->capital = $user->capital + $item->price;
        $user->save();

        ProductHistory::create([
            'product_name' => $item->name,
            'action' => 'quantity decreased',
            'changed_data' => 'quantity decreased to ' . $item->quantity,
        ]);

        Transaction::create([
            'user_name' => $user->name,
            'product_name' => $item->name,
            'quantity' => -1,
            'price' => $item->price,
            'total_amount' => -$item->price,
        ]);

        return redirect()->route('dashboard')->with('success', 'Item updated successfully.');
    }

    public function updateProduct(Request $request, $id){
        
        $userid = $request->user()->id;
        $validatedData = request()->validate([
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
            'user_id' => $userid,
            'is_archived' => $validatedData['is_archived'],
            'file_path' => $product->file_path,
        ]);
        
        $product->save();

        if (!empty($changedData)) {
        ProductHistory::create([
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
    $product = Product::findOrFail($id);  

    ProductHistory::create([
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



    public function deletePHistory($id)
    {
    $history = ProductHistory::findOrFail($id);    
    $history->delete();
    return redirect()->route('dashboard');
    }

    public function addCapital(Request $request){
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|string'            
        ]);
        $user = $request->user();
        $validatedData['amount'] = (float) $validatedData['amount'];
        $amount = $user->capital;

        if($validatedData['type'] == 'add'){
            $newAmount = $amount + $validatedData['amount'];
        } else if($validatedData['type'] == 'withdraw'){
            $newAmount = $amount - $validatedData['amount'];
        } else{
            $newAmount = $validatedData['amount'];
        }

        $user->update(['capital' => $newAmount]);

        return redirect()->route('dashboard');
    }
}

