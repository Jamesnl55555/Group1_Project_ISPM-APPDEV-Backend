<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExcelController;
use App\Models\Capital;
use App\Models\Product;
use App\Models\Transaction;
use GuzzleHttp\Middleware;
use App\Http\Controllers\Auth\PendingRegistrationController;

Route::post('/register-pending', [PendingRegistrationController::class, 'store']);
Route::get('/register/confirm', [PendingRegistrationController::class, 'confirm']);

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
    return $request->user();
    })->name('user');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout'); 
    
    Route::get('/fetchproducts', function () {
    $products = Product::latest()->take(10)->get();
        return response()->json([
            'success' => true,
            'products' => $products ?: [],
        ]);
    })->name('fetchproducts');
    
    Route::get('/fetchtransactions', function () {
    $transactions = Transaction::latest()->take(10)->get();
        return response()->json([
            'success' => true,
            'transactions' => $transactions ?: [],
        ]);
    })->name('fetchtransactions');

    Route::get('/fetchcapital', function () {
        try {
        $capitals = Capital::latest()->take(10)->get();
        return response()->json([
            'success' => true,
            'capitals' => $capitals,
        ]);
        }catch (\Exception $e) {
        Log::error('FetchCapital error: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch capitals',
            'capitals' => [],
        ], 500);
        }
    })->name('fetchcapital');
    
    Route::post('/import', [ExcelController::class, 'import'])->name('import');
    Route::post('/postproducts', [InventoryController::class, 'addItem'])->name('postproducts'); 
    Route::post('/update-product/{id}', [InventoryController::class, 'updateProduct'])->name('update-product');
    Route::post('/delete-item/{id}', [InventoryController::class, 'deleteItem'])->name('delete-item');
    Route::post('/checkout', [InventoryController::class, 'checkout'])->name('checkout');
    
    Route::get('/email/verify-status', function (Request $request) {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail(),
        ]);
    })->name('verification.status');

    Route::post('/email/verification-notification', 
        [EmailVerificationNotificationController::class, 'store']
    )->middleware('throttle:6,1')
     ->name('verification.send');
    
    Route::get('/verify-email/{id}/{hash}', 
        [VerifyEmailController::class, '__invoke']
    )->middleware(['signed', 'throttle:6,1'])
     ->name('verification.verify');

     Route::post('/confirm-password', 
        [ConfirmablePasswordController::class, 'store']
    );

    Route::put('/password', 
        [PasswordController::class, 'update']
    )->name('password.update');
});