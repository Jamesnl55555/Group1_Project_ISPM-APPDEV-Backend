<?php

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesReportController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\SendCodeController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ExcelController;
use App\Models\Capital;
use App\Models\Product;
use App\Models\Transaction;
use GuzzleHttp\Middleware;
use App\Http\Controllers\Auth\PendingRegistrationController;
use App\Http\Controllers\CapitalReportController;
use App\Http\Middleware\RefreshTokenExpiration;
use App\Http\Controllers\UpdateProfileController;
use Illuminate\Support\Facades\DB;

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register-pending', [PendingRegistrationController::class, 'store']);
Route::get('/register/confirm', [PendingRegistrationController::class, 'confirm']);

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::post('/forgot-password', [SendCodeController::class, 'sendResetCode'])->name('password.email')->middleware('throttle:3,1');
Route::post('/verify-code', [VerifyEmailController::class, 'verify'])->name('password.verify-code')->middleware('throttle:3,1');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');



Route::get('/api/sign-upload', function () {
    $timestamp = time();
    $params = [
        'timestamp' => $timestamp,
    ];
    ksort($params);
    $paramString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $signature = sha1($paramString . env('CLOUDINARY_API_SECRET'));

    return response()->json([
        'signature' => $signature,
        'timestamp' => $timestamp,
        'api_key' => env('CLOUDINARY_API_KEY'),
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    ]);
});

Route::middleware('auth:sanctum', RefreshTokenExpiration::class)->group(function () {

    Route::get('/user', function (Request $request) {
    return $request->user();
    })->name('user');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout'); 
    Route::get('/amounts', [ChartController::class, 'amountsOverTime']);
    Route::get('/fetchtotaltransactions', [TransactionsController::class, 'fetchTotalAmount']);
    
    Route::get('/fetchproducts', function (Request $request) {
        $user = $request->user();

        $query = Product::where('user_id', $user->id)->where('is_archived', 0);

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }
        $products = $query->orderBy('id', 'desc')->paginate(10);
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

    $product = Product::where('id', $id)->where('user_id', $user->id)->first();
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

    $query = Transaction::where('user_id', $user->id)
        ->select(
            'transaction_number',
            DB::raw('SUM(total_amount) as total_amount'),
            DB::raw('MAX(created_at) as created_at')
        )
        ->groupBy('transaction_number');

    if ($request->has('search') && $request->search != '') {
        $query->having('transaction_number', 'like', '%' . $request->search . '%');
    }

    $transactions = $query
        ->orderByDesc('transaction_number')
        ->paginate(10);

    return response()->json([
        'transactions' => $transactions->items(),
        'current_page' => $transactions->currentPage(),
        'last_page' => $transactions->lastPage(),
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

    Route::put('/update-transaction/{id}', function (Request $request, $id) {
    $user = $request->user();

    $validatedData = $request->validate([
        'total_amount' => 'required|numeric',
        'payment_method' => 'nullable|string|max:255',
        'file_path' => 'nullable|string',
    ]);

    $transaction = Transaction::where('user_name', $user->name)->findOrFail($id);

    $changedData = [];
    foreach (['total_amount', 'payment_method', 'file_path'] as $field) {
        $newValue = $validatedData[$field] ?? null;
        if ($transaction->$field != $newValue) {
            $changedData[] = ucfirst($field) . " changed from '{$transaction->$field}' to '{$newValue}'";
        }
    }

    $transaction->fill($validatedData);
    $transaction->save();

    return response()->json([
        'success' => true,
        'message' => 'Transaction updated successfully.',
        'changes' => $changedData
    ]);
    });

    Route::delete('/delete-transaction/{id}', function (Request $request, $id) {
    $user = $request->user();

    $transaction = Transaction::where('user_name', $user->name)->findOrFail($id);

    $transaction->delete();

    return response()->json([
        'success' => true,
        'message' => 'Transaction deleted successfully.'
    ]);
    });
    Route::post('/postproducts', [InventoryController::class, 'addItem'])->name('postproducts'); 
    Route::post('/update-product/{id}', [InventoryController::class, 'updateProduct'])->name('update-product');
    Route::post('/delete-item/{id}', [InventoryController::class, 'deleteItem'])->name('delete-item');
    Route::post('/checkout', [InventoryController::class, 'checkout'])->name('checkout');
    Route::post('/unarchive/{id}', [InventoryController::class, 'unarchiveItem']);
    Route::post('/import', [ExcelController::class, 'import'])->name('import');

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

    Route::post('/confPass', [PasswordController::class, 'confirmPass']);
    Route::put('/changePass', [PasswordController::class, 'changePass']);

    Route::put('/editprofile', [UpdateProfileController::class, 'update']);
    Route::get('/fetchLatestTransactions', [TransactionsController::class, 'fetchLatestTransactions']);
    Route::get('/fetchLatestProductNumber', function (Request $request) {
    $user = $request->user();
    $latestProduct = Product::where('user_id', $user->id)
        ->max('product_number');

    return response()->json(['latest_product_number' => $latestProduct ?? 0]);
    }
    );
    Route::get('/fetchLatestTransactionNumber', function (Request $request) {
    $user = $request->user();
    $latestTransaction = Transaction::where('user_name', $user->name)
        ->max('transaction_number');
    return response()->json(['latest_transaction_number' => $latestTransaction ?? 0]);
    }
    );
    Route::get('/fetchTransactionNumber', function (Request $request) {
    $request->validate([
        'transaction_number' => 'required'
    ]);

    $user = $request->user();
    
    $transactions = Transaction::where('user_name', $user->name)
        ->where('transaction_number', $request->transaction_number)
        ->get();
    return response()->json(['items' => $transactions]);
    }
    );
});