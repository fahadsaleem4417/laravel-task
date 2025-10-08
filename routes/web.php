<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\StockPriceApiController;
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [StockPriceController::class, 'showForm']);
Route::post('import', [StockPriceController::class, 'import'])->name('stock.import');

Route::prefix('api')->middleware('api')->group(function () {
    Route::get('/stock-performance', [StockPriceApiController::class, 'getPerformance']);
    Route::get('/stock-change-period', [StockPriceApiController::class, 'getChangeBetweenDates']);
});
