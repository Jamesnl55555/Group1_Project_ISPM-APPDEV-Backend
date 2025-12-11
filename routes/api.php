<?php


use Illuminate\Http\Request;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesReportController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ChartController;
use App\Models\Capital;
use App\Models\Product;
use App\Models\Transaction;
use GuzzleHttp\Middleware;
use App\Http\Controllers\Auth\PendingRegistrationController;
use App\Http\Controllers\CapitalReportController;

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
    Route::get('/amounts', [ChartController::class, 'amountsOverTime']);
    Route::get('/fetchtotaltransactions', [TransactionsController::class, 'fetchTotalAmount']);
    Route::get('/fetchproducts', function (Request $request) {
    $user = $request->user();
    $products = Product::where('user_id', $user->id) // only the current user's products
        ->where('is_archived', 0)
        ->orderBy('id', 'desc')
        ->paginate(10);

    return response()->json([
        'products' => $products->items(),
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
    ]);
    });

    Route::get('/fetchproducts-lowstock', function (Request $request) {
    $user = $request->user();

    $products = Product::where('user_id', $user->id) // only the current user's products
        ->where('is_archived', 0)
        ->where('quantity', '<=', 20)
        ->orderBy('id', 'desc')
        ->paginate(10);

    return response()->json([
        'products' => $products->items(),
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
    ]);
    });

    Route::get('/fetchproducts-archived', function (Request $request) {
    $user = $request->user();
    $products = Product::where('user_id', $user->id)
        ->where('is_archived', 1)
        ->orderBy('id', 'desc')
        ->paginate(10);

    return response()->json([
        'products' => $products->items(),
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
    ]);
    });


    Route::get('/latest-transaction', [InventoryController::class, 'fetchLatestTransaction'])->name('latest-transaction');
    Route::get('/latest-transactions', [TransactionsController::class, 'fetchLatestThreeTransactions'])->name('latest-transaction');
    Route::get('/low-stock', [ProductController::class, 'countLowStockProducts'])->name('low-stock-count');
    Route::get('/user-products', [ProductController::class, 'getUserProducts'])->name('latest-products');

    Route::get('/fetchproduct/{id}', function (Request $request, $id) {
    $user = $request->user();

    $product = Product::where('id', $id)
                      ->where('user_id', $user->id)
                      ->first();
    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'product' => $product
    ]);
    })->name('fetchproduct');
    
    Route::post('/archive-item/{id}', [InventoryController::class, 'archiveItem'])->name('archive-product');   
    
    Route::get('/fetchtransactions', function (Request $request) {
    $user = $request->user();
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);

    $transactions = Transaction::where('user_name', $user->name)
        ->latest()
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'success' => true,
        'transactions' => $transactions->items(),
        'current_page' => $transactions->currentPage(),
        'last_page' => $transactions->lastPage(),
        'per_page' => $transactions->perPage(),
        'total' => $transactions->total(),
    ]);
    });

    Route::get('/fetchcapital', function (Request $request) {
    $user = $request->user();
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);

    $capitals = Capital::where('user_id', $user->id)
        ->latest()
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'success' => true,
        'capitals' => $capitals->items(),
        'current_page' => $capitals->currentPage(),
        'last_page' => $capitals->lastPage(),
        'per_page' => $capitals->perPage(),
        'total' => $capitals->total(),
    ]);
    });

    Route::post('/import', [ExcelController::class, 'import'])->name('import');
    Route::post('/postproducts', [InventoryController::class, 'addItem'])->name('postproducts'); 
    Route::post('/update-product/{id}', [InventoryController::class, 'updateProduct'])->name('update-product');
    Route::post('/delete-item/{id}', [InventoryController::class, 'deleteItem'])->name('delete-item');
    Route::post('/checkout', [InventoryController::class, 'checkout'])->name('checkout');
    Route::post('/unarchive/{id}', [InventoryController::class, 'unarchiveItem']);
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

    Route::get('/fetch-daily', [SalesReportController::class, 'fetchDaily']);
    Route::get('/fetch-weekly', [SalesReportController::class, 'fetchWeekly']);
    Route::get('/fetch-monthly', [SalesReportController::class, 'fetchMonthly']);
    Route::get('/fetch-custom', [SalesReportController::class, 'fetchCustom']);

    Route::get('/capital-daily', [CapitalReportController::class, 'fetchDaily']);
    Route::get('/capital-weekly', [CapitalReportController::class, 'fetchWeekly']);
    Route::get('/capital-monthly', [CapitalReportController::class, 'fetchMonthly']);
    Route::get('/capital-custom', [CapitalReportController::class, 'fetchCustom']);

    Route::put('/password', 
        [PasswordController::class, 'update']
    )->name('password.update');
});