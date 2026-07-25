<?php

use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\ScanBarcodeController;
use App\Http\Controllers\Web\StockController;
use App\Http\Controllers\Web\StockMutationController;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\VendorController;

// ===locations===

Route::get('/location', [LocationController::class, 'index'])
    ->name('locations.index');

Route::post('/location', [LocationController::class, 'store'])
    ->name('locations.store');

Route::put('/location/{location}', [LocationController::class, 'update'])
    ->name('locations.update');

Route::delete('/location/{location}', [LocationController::class, 'destroy'])
    ->name('locations.destroy');

Route::get('/check-location-code', function (Request $request) {

    $query = Location::where('location_code', $request->code);

    if ($request->filled('ignore_id')) {
        $query->where('id', '!=', $request->ignore_id);
    }

    return response()->json([
        'exists' => $query->exists(),
    ]);
});

// ==stocks==

Route::get('/stock', [StockController::class, 'index'])->name('stock.index');

Route::post('/stock', [StockController::class, 'store'])->name('stock.store');

Route::get('/stock/template', [StockController::class, 'downloadTemplate'])
    ->name('stock.template');

Route::post('/stock/import', [StockController::class, 'import'])
    ->name('stock.import');

Route::delete('/stock/{stock}', [StockController::class, 'destroy'])
    ->name('stocks.destroy');

Route::get('/stock/{stock}/edit', [StockController::class, 'edit'])
    ->name('stock.edit');

Route::put('/stock/{stock}', [StockController::class, 'update'])
    ->name('stock.update');

Route::get('/stock/export', [StockController::class, 'export'])
    ->name('stock.export');

Route::get('stock/data', [StockController::class, 'data'])
    ->name('stock.data');

// ===scan===
Route::get('/scan-location', [ScanBarcodeController::class, 'index'])
    ->name('scan_location');

Route::post('/scan-location/search', [ScanBarcodeController::class, 'search'])
    ->name('scan.location.search');

// ===mutation===

Route::get('/stock/{stock}/mutations', [StockController::class, 'mutations'])
    ->name('stock.mutations');

Route::get('/stock-mutation', [StockMutationController::class, 'index'])
    ->name('stock-mutation.index');

Route::get('/stock-mutation/template', [StockMutationController::class, 'downloadTemplate'])
    ->name('stock-mutation.template');

Route::post('/stock-mutation/import', [StockMutationController::class, 'import'])
    ->name('stock-mutation.import');

Route::delete('/stock-mutation/{stockMutation}', [StockMutationController::class, 'destroy'])
    ->name('stock-mutation.destroy');

Route::get('/stock-mutation/data', [StockMutationController::class, 'data'])
    ->name('stock-mutation.data');

Route::prefix('stock-mutation')->group(function () {

    Route::get('/search-stock', [StockMutationController::class, 'searchStock'])
        ->name('stock-mutation.search-stock');

    Route::get('/current-stock', [StockMutationController::class, 'currentStock'])
        ->name('stock-mutation.current-stock');

    Route::post('/store', [StockMutationController::class, 'store'])
        ->name('stock-mutation.store');

    Route::get('/{mutation}/edit', [StockMutationController::class, 'edit'])
        ->name('stock-mutation.edit');

    Route::put('/{mutation}', [StockMutationController::class, 'update'])
        ->name('stock-mutation.update');

    Route::get('/export', [StockMutationController::class, 'export'])
        ->name('mutation.export');

    Route::get('/default-location', [StockMutationController::class, 'defaultLocation'])
        ->name('stock-mutation.default-location');
});


Route::get('/vendor', [VendorController::class, 'index'])
    ->name('vendors.index');

Route::post('/vendor', [VendorController::class, 'store'])
    ->name('vendors.store');

Route::put('/vendor/{vendor}', [VendorController::class, 'update'])
    ->name('vendors.update');

Route::delete('/vendor/{vendor}', [VendorController::class, 'destroy'])
    ->name('vendors.destroy');
