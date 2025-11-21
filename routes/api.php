<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExcelController;
use App\Models\Product;
use App\Models\Transaction;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('web')->group(function () {
Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout'); 
    
    Route::get('/fetchproducts', function () {
    $products = Product::latest()->take(10)->get();
        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    })->name('fetchproducts');
    Route::get('/fetchtransactions', function () {
    $transactions = Transaction::latest()->take(10)->get();
        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    })->name('fetchtransactions');
    
    Route::post('/import', [ExcelController::class, 'import'])->name('import');
    Route::post('/postproducts', [InventoryController::class, 'addItem'])->name('postproducts'); 
    Route::post('/update-product/{id}', [InventoryController::class, 'updateProduct'])->name('update-product');
    Route::post('/delete-item/{id}', [InventoryController::class, 'deleteItem'])->name('delete-item');
    Route::post('/checkout', [InventoryController::class, 'checkout'])->name('checkout');

        
});