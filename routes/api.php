<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Sunat\SummaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



Route::apiResource('products', ProductController::class)->names('api.products');

Route::apiResource('sales', SaleController::class)->names('api.sales');

Route::post('sales/convert-invoice', [SaleController::class, 'convertInvoice'])->name('api.sales.convert-invoice');
Route::post('sales/batch-convert-invoice', [SaleController::class, 'batchConvertInvoice'])->name('api.sales.batch-convert-invoice');


Route::get('dashboard', [DashboardController::class, 'index'])->name('api.dashboard');


Route::prefix('sunat')->group(function () {

    Route::prefix('summary')->group(function () {
        Route::get('index', [SummaryController::class, 'index'])->name('api.sunat.summary.index');
        Route::post('store', [SummaryController::class, 'store'])->name('api.sunat.summary.store');
        Route::post('get-documents', [SummaryController::class, 'getDocuments'])->name('api.sunat.summary.get-documents');
    });

});


