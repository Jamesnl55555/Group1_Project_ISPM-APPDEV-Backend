<?php
require __DIR__.'/auth.php';

// use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\InventoryController;
// use App\Http\Controllers\ExcelController;
// use App\Http\Controllers\MakeTransactionController;
// use App\Http\Controllers\AddProductController;
// use App\Http\Controllers\TransactionRecSectionController;
// use App\Http\Controllers\TransactionController;
// use App\Http\Controllers\InventoryProductController;
// use App\Models\Product;
// use App\Models\Transaction;
// use App\Models\ProductHistory;
// use App\Models\UserHistory;
// use App\Models\TransactionHistory;
use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Artisan;





// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register')]);
// });

// Route::get('/dashboard', function (Request $request) {
//     $user = $request->user()->load(['products']);
//     $tRecords = Transaction::latest()->take(50)->get();
//     $tHistory = TransactionHistory::latest()->take(50)->get();
//     $pHistory = ProductHistory::latest()->take(50)->get();
//     $uHistory = UserHistory::latest()->take(50)->get();
    
//     return Inertia::render('Dashboard', ['user' => $user, 'tHistory' => $tHistory, 'tRecords'=> $tRecords, 'pHistory' => $pHistory, 'uHistory' => $uHistory]);
//     })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('/fetchproducts', [AddProductController::class, 'fetchProducts'])->name('fetchproducts');
//     Route::get('/fetchtransactions', [TransactionController::class, 'fetchTransactions'])->name('fetchtransactions');

    // Existing Sales Report page
    // Route::get('/sales-report', function () {
    //     $transactions = Transaction::latest()->take(10)->get();
    //     return Inertia::render('Reports/SalesReport', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('sales-report');

    // Generate Sales Report
    // Route::get('/generate-sales-report', function () {
    //     $transactions = Transaction::latest()->get();
    //     return Inertia::render('Reports/GenerateSalesReport', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-sales-report');

    // Daily Sales Report
    // Route::get('/generate-sales-report/daily', function () {
    //     $transactions = Transaction::whereDate('created_at', today())->get();
    //     return Inertia::render('SalesReports/GenerateSalesReportDaily', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-sales-report.daily');

    // Weekly Sales Report
    // Route::get('/generate-sales-report/weekly', function () {
    //     $startOfWeek = now()->startOfWeek();
    //     $endOfWeek = now()->endOfWeek();
    //     $transactions = Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
    //     return Inertia::render('SalesReports/GenerateSalesReportWeekly', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-sales-report.weekly');

    // Monthly Sales Report
    // Route::get('/generate-sales-report/monthly', function () {
    //     $transactions = Transaction::whereMonth('created_at', now()->month)->get();
    //     return Inertia::render('SalesReports/GenerateSalesReportMonthly', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-sales-report.monthly');

    // Custom Sales Report
    // Route::get('/generate-sales-report/custom', function (Request $request) {
    //     $from = $request->input('from');
    //     $to = $request->input('to');
    //     $transactions = Transaction::whereBetween('created_at', [$from, $to])->get();
    //     return Inertia::render('SalesReports/GenerateSalesReportCustom', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-sales-report.custom');

    // Generate Capital Report
    // Route::get('/generate-capital-report', function () {
    //     $transactions = Transaction::latest()->get();
    //     return Inertia::render('Reports/GenerateCapitalReport', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-capital-report');

    // Daily Capital Report
    // Route::get('/generate-capital-report/daily', function () {
    //     $transactions = Transaction::where('type', 'capital')
    //         ->whereDate('created_at', today())
    //         ->get();
    //     return Inertia::render('CapitalReports/GenerateCapitalReportDaily', [
    //         'transactions' => $transactions,
    //     ]);
    // })->name('generate-capital-report.daily');

    // Weekly Capital Report
//     Route::get('/generate-capital-report/weekly', function () {
//         $startOfWeek = now()->startOfWeek();
//         $endOfWeek = now()->endOfWeek();
//         $transactions = Transaction::where('type', 'capital')
//             ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
//             ->get();
//         return Inertia::render('CapitalReports/GenerateCapitalReportWeekly', [
//             'transactions' => $transactions,
//         ]);
//     })->name('generate-capital-report.weekly');

//     // Monthly Capital Report
//     Route::get('/generate-capital-report/monthly', function () {
//         $transactions = Transaction::where('type', 'capital')
//             ->whereMonth('created_at', now()->month)
//             ->get();
//         return Inertia::render('CapitalReports/GenerateCapitalReportMonthly', [
//             'transactions' => $transactions,
//         ]);
//     })->name('generate-capital-report.monthly');

//     // Custom Capital Report
//     Route::get('/generate-capital-report/custom', function (Request $request) {
//         $from = $request->input('from');
//         $to = $request->input('to');
//         $transactions = Transaction::where('type', 'capital')
//             ->whereBetween('created_at', [$from, $to])
//             ->get();
//         return Inertia::render('CapitalReports/GenerateCapitalReportCustom', [
//             'transactions' => $transactions,
//         ]);
//     })->name('generate-capital-report.custom');

// });

// Route::middleware(['auth', 'verified'])->group(function () {
//     // Transaction Record main page
//     Route::get('/transaction-rec-section', [TransactionRecSectionController::class, 'index'])
//         ->name('transaction-rec-section');
// });

// Route::get('/transaction-record', function () {
//     $transactions = Transaction::latest()->take(10)->get();

//     return Inertia::render('Reports/TransactionRecord', [
//         'transactions' => $transactions,
//     ]);
// })->name('transaction-record');

// Route::get('/inventory1', function () {
//     $products = Product::latest()->take(10)->get();
//     return Inertia::render('Reports/Inventory1', [
//         'products' => $products,
//     ]);
// })->name('inventory1');

// Route::get('/inventory2', function () {
//     $transactions = Transaction::latest()->take(10)->get();

//     return Inertia::render('Reports/Inventory2', [
//         'transactions' => $transactions,
//     ]);
// })->name('inventory2');
//     });


// Route::get('/inventory/create', [InventoryProductController::class, 'create'])->name('inventory.create');
// Route::post('/inventory/store', [InventoryProductController::class, 'store'])->name('inventory.store');
// Route::put('/inventory/{id}', [InventoryProductController::class, 'update'])->name('inventory.update');
// Route::delete('/inventory/{id}', [InventoryProductController::class, 'destroy'])->name('inventory.destroy');
// Route::get('/make-transaction', [MakeTransactionController::class, 'index'])->name('make-transaction');

// Route::get('/add-product', function () {
//     return Inertia::render('QA/AddProduct');
// })->name('add-product');


// Route::get('/generate-report', function () {
//     $transactions = Transaction::latest()->take(10)->get();

//     return Inertia::render('QA/GenerateReport', [
//         'transactions' => $transactions,
//     ]);
// })->name('generate-report');
    
// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//     Route::post('/add-item', [InventoryController::class, 'addItem'])->name('add-item');
//     Route::get('/user/{id}', function($id) {
//         $transaction = Transaction::find($id); 
//         return Inertia::render('TransactionDetails', ['transaction' => $transaction]);
//     })->name('transactiondetails');
//     Route::put('/update-item-inc/{id}', [InventoryController::class, 'updateItemInc'])->name('update-iteminc');
//     Route::put('/update-item-dec/{id}', [InventoryController::class, 'updateItemDec'])->name('update-itemdec');
//     Route::get('/edit-product/{id}', [AddProductController::class, 'editProduct'])->name('edit-product');
//     Route::post('/update-product/{id}', [InventoryController::class, 'updateProduct'])->name('update-product');
//     Route::post('/edit-item/{id}', [InventoryController::class, 'editItem'])->name('edit-item');
//     Route::post('/delete-item/{id}', [InventoryController::class, 'deleteItem'])->name('delete-item');
//     Route::post('/delete-phistory/{id}', [InventoryController::class, 'deletePHistory'])->name('delete-phistory');
//     Route::post('/add-capital', [InventoryController::class, 'addCapital'])->name('add-capital');
//     Route::post('/checkout', [InventoryController::class, 'checkout'])->name('checkout');
//     Route::post('/import', [ExcelController::class, 'import'])->name('import');
//     Route::get('/create-transaction', [TransactionController::class, 'createTransaction'])->name('create-transaction');

// });

// require __DIR__.'/auth.php';