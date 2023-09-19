<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NlpController;
use App\Http\Controllers\SuccessController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\Transaction;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('admin.index');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [Transaction::class, 'transaction'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // store attendance
    Route::post('/store-attendance',[AttendanceController::class, 'storedAttendance'])->name('store.attendance');

    // inventory
    Route::get('/available-stocks',[TableController::class,'availableStocks'])->name('inventory.available.stocks');
    // show product sold
    Route::get('/show-product-sold',[TableController::class,'purchasedProduct'])->name('inventory.product.sold');
    Route::get('/process-product-sold/{id}',[TableController::class,'processProduct'])->name('inventory.process.sold');
    // show product rejected
    Route::get('/show-product-rejected',[TableController::class,'rejectedProduct'])->name('inventory.product.rejected');
    // show product rejected
    Route::get('/show-product-outofstocks',[TableController::class,'outofstocksProduct'])->name('inventory.product.outofstocks');
    // show product page
    Route::get('/show-product-page',[InventoryController::class,'showProductPage'])->name('show.product.page');
    // add bulk product or single
    Route::post('/manage-products',[InventoryController::class,'manageProducts'])->name('bulk.manage.stocks');
    // create invoice
    Route::post('/create-invoices',[InvoiceController::class,'createInvoices'])->name('bulk.create.invoice');


    // transaction 
    // Route::get('/transaction',[Transaction::class,'transaction'])->name('transaction');

    // employee
    Route::get('/employee',[EmployeeController::class,'employeeTable'])->name('employee.table');

    // nlp server route
    Route::post('/nlp',[NlpController::class,'process'])->name('nlp.process');

});

// attendance
Route::get('/scan', [AttendanceController::class, 'showScanPage'])->name('scan.attendance');
Route::post('/scan', [AttendanceController::class, 'processScan'])->name('process-scan');
Route::post('/upload', [AttendanceController::class, 'uploadQR'])->name('upload-qr');
Route::get('/attendance', [AttendanceController::class, 'viewAttendance']);

// verification success page
Route::get('/success',[SuccessController::class,'verificationSuccess'])->name('verification.success');

require __DIR__.'/auth.php';
