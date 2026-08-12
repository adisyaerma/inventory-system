<?php

use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\ScanBarcodeController;
use App\Http\Controllers\Web\StagingInController;
use App\Http\Controllers\Web\StagingOutController;
use App\Http\Controllers\Web\StockController;
use App\Http\Controllers\Web\StockMutationController;
use App\Http\Controllers\Web\VendorController;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===locations===

Route::get('/location', [LocationController::class, 'index'])
    ->name('locations.index');

Route::post('/location', [LocationController::class, 'store'])
    ->name('locations.store');

Route::put('/location/{location}', [LocationController::class, 'update'])
    ->name('locations.update');

Route::delete('/location/bulk/destroy', [LocationController::class, 'bulkDestroy'])
    ->name('locations.bulk-destroy');

Route::delete('/location/{location}', [LocationController::class, 'destroy'])
    ->name('locations.destroy');

Route::get('locations/data', [LocationController::class, 'data'])->name('locations.data');
Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');

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

Route::delete('/stock/bulk/destroy', [StockController::class, 'bulkDestroy'])
    ->name('stocks.bulk-destroy');

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

Route::delete('/stock-mutation/bulk/destroy', [StockMutationController::class, 'bulkDestroy'])
    ->name('stock-mutation.bulk-destroy');

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

// ===vendor===

Route::get('/vendor', [VendorController::class, 'index'])
    ->name('vendors.index');

Route::post('/vendor', [VendorController::class, 'store'])
    ->name('vendors.store');

Route::put('/vendor/{vendor}', [VendorController::class, 'update'])
    ->name('vendors.update');

Route::delete('/vendor/{vendor}', [VendorController::class, 'destroy'])
    ->name('vendors.destroy');

// ===staging in===

Route::get('/stagings-in/data', [StagingInController::class, 'data'])
    ->name('stagings-in.data');

Route::get('stagings-in/template', [StagingInController::class, 'template'])
    ->name('stagings-in.template');

Route::post('stagings-in/import', [StagingInController::class, 'import'])
    ->name('stagings-in.import');

Route::get('/staging-in', [StagingInController::class, 'index'])
    ->name('stagings-in.index');

Route::post('/staging-in', [StagingInController::class, 'store'])
    ->name('stagings-in.store');

Route::get('/staging-in/{staging}/edit', [StagingInController::class, 'edit'])
    ->name('stagings-in.edit');

Route::put('/staging-in/{staging}', [StagingInController::class, 'update'])
    ->name('stagings-in.update');

Route::delete('/staging-in/bulk/destroy', [StagingInController::class, 'bulkDestroy'])
    ->name('stagings-in.bulk-destroy');

Route::delete('/staging-in/{staging}', [StagingInController::class, 'destroy'])
    ->name('stagings-in.destroy');

Route::get('stagings-in/export', [StagingInController::class, 'export'])->name('stagings-in.export');

Route::post('stagings-in/{staging}/move-to-stock', [StagingInController::class, 'moveToStock'])
    ->name('stagings-in.move-to-stock');

Route::post('stagings-in/{staging}/move-to-staging-out', [StagingInController::class, 'moveToStagingOut'])
    ->name('stagings-in.move-to-staging-out');

Route::get('stagings-in/warehouse-locations', [StagingInController::class, 'warehouseLocations'])
    ->name('stagings-in.warehouse-locations');

Route::get('stagings-in/search-stock', [StagingInController::class, 'searchStock'])
    ->name('stagings-in.search-stock');

// ===staging out===

Route::get('/stagings-out/data', [StagingOutController::class, 'data'])
    ->name('stagings-out.data');

Route::get('stagings-out/template', [StagingOutController::class, 'template'])
    ->name('stagings-out.template');

Route::post('stagings-out/import', [StagingOutController::class, 'import'])
    ->name('stagings-out.import');

Route::get('/staging-out', [StagingOutController::class, 'index'])
    ->name('stagings-out.index');

Route::post('/staging-out', [StagingOutController::class, 'store'])
    ->name('stagings-out.store');

Route::get('/staging-out/{stagingOut}/edit', [StagingOutController::class, 'edit'])
    ->name('stagings-out.edit');

Route::put('/staging-out/{stagingOut}', [StagingOutController::class, 'update'])
    ->name('stagings-out.update');

Route::delete('/staging-out/bulk/destroy', [StagingOutController::class, 'bulkDestroy'])
    ->name('stagings-out.bulk-destroy');

Route::delete('/staging-out/{stagingOut}', [StagingOutController::class, 'destroy'])
    ->name('stagings-out.destroy');

Route::get('stagings-out/export', [StagingOutController::class, 'export'])->name('stagings-out.export');
