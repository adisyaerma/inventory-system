<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\LocationStockController;
use App\Http\Controllers\Web\ScanBarcodeController;
use App\Http\Controllers\Web\StagingInController;
use App\Http\Controllers\Web\StagingOutController;
use App\Http\Controllers\Web\StockMutationController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\StagingInHistoryController;
use App\Http\Controllers\Web\StagingOutHistoryController;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ItemHistoryController;


// ===locations===

Route::get('/location', [LocationController::class, 'index'])
    ->name('locations.index');

Route::get('locations/preview', [LocationController::class, 'preview'])->name('locations.preview');

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

// ==items==

Route::get('/item', [ItemController::class, 'index'])->name('items.index');

Route::post('/item', [ItemController::class, 'store'])->name('items.store');

Route::delete('/item/bulk/destroy', [ItemController::class, 'bulkDestroy'])
    ->name('items.bulk-destroy');

Route::delete('/item/{item}', [ItemController::class, 'destroy'])
    ->name('items.destroy');

Route::get('/item/{item}/edit', [ItemController::class, 'edit'])
    ->name('items.edit');

Route::put('/item/{item}', [ItemController::class, 'update'])
    ->name('items.update');

Route::get('item/data', [ItemController::class, 'data'])
    ->name('items.data');

// ==location stock==

Route::get('/location-stock', [LocationStockController::class, 'index'])
    ->name('location-stock.index');

Route::get('/location-stock/data', [LocationStockController::class, 'data'])
    ->name('location-stock.data');

Route::get('/location-stock/search-item', [LocationStockController::class, 'searchItem'])
    ->name('location-stock.search-item');

Route::post('/location-stock', [LocationStockController::class, 'store'])
    ->name('location-stock.store');

Route::delete('/location-stock/bulk/destroy', [LocationStockController::class, 'bulkDestroy'])
    ->name('location-stock.bulk-destroy');

Route::get('/location-stock/{item}/edit', [LocationStockController::class, 'edit'])
    ->name('location-stock.edit');

Route::put('/location-stock/{item}', [LocationStockController::class, 'update'])
    ->name('location-stock.update');

Route::delete('/location-stock/{item}', [LocationStockController::class, 'destroy'])
    ->name('location-stock.destroy');

Route::get('location-stock/export', [LocationStockController::class, 'export'])->name('location-stock.export');
Route::get('location-stock/template', [LocationStockController::class, 'downloadTemplate'])->name('location-stock.template');
Route::post('location-stock/import', [LocationStockController::class, 'import'])->name('location-stock.import');

// ===scan===
Route::get('/scan-location', [ScanBarcodeController::class, 'index'])
    ->name('scan_location');

Route::post('/scan-location/search', [ScanBarcodeController::class, 'search'])
    ->name('scan.location.search');

// ===mutation===

Route::get('/item/{item}/mutations', [ItemController::class, 'mutations'])
    ->name('item.mutations');

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

Route::get('stock-mutation/lots', [StockMutationController::class, 'lots'])->name('stock-mutation.lots');

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

Route::get('stagings-in/item-locations', [StagingInController::class, 'itemLocations'])
    ->name('stagings-in.item-locations');

Route::get('stagings-in/lots', [StagingInController::class, 'lots'])->name('stagings-in.lots');

Route::patch('stagings-in/{staging}/location', [StagingInController::class, 'updateLocation'])
    ->name('stagings-in.update-location');

Route::get('stagings-in/location-lots', [StagingInController::class, 'locationLots'])
    ->name('stagings-in.location-lots');

Route::get('stagings-in-history/{history}/export-detail', [StagingInHistoryController::class, 'exportDetail'])
    ->name('stagings-in-history.export-detail');

Route::post('stagings-in-history/bulk-restore', [StagingInHistoryController::class, 'bulkRestore'])
    ->name('stagings-in-history.bulk-restore');
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

Route::get('stagings-out/search-stock', [StagingOutController::class, 'searchStock'])->name('stagings-out.search-stock');

Route::get('stagings-out/search-location-for-item', [StagingOutController::class, 'searchLocationForItem'])->name('stagings-out.search-location-for-item');
Route::get('stagings-out/search-lot-for-item-location', [StagingOutController::class, 'searchLotForItemLocation'])->name('stagings-out.search-lot-for-item-location');

Route::patch('stagings-out/{stagingOut}/location', [StagingOutController::class, 'updateLocation'])
    ->name('stagings-out.update-location');

Route::post('stagings-out/import-stock', [StagingOutController::class, 'importStock'])
    ->name('stagings-out.import-stock');

Route::post('stagings-in-history/bulk-destroy', [StagingInHistoryController::class, 'bulkDestroy'])
    ->name('stagings-in-history.bulk-destroy');

Route::post('stagings-out-history/bulk-destroy', [StagingOutHistoryController::class, 'bulkDestroy'])
    ->name('stagings-out-history.bulk-destroy');
    
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('stagings-in-history', [StagingInHistoryController::class, 'index'])->name('stagings-in-history.index');
Route::get('stagings-in-history/data', [StagingInHistoryController::class, 'data'])->name('stagings-in-history.data');
Route::get('stagings-in-history/{history}/detail', [StagingInHistoryController::class, 'detail'])->name('stagings-in-history.detail');
Route::get('stagings-in-history/export', [StagingInHistoryController::class, 'export'])->name('stagings-in-history.export'); // opsional, lihat poin 3

Route::prefix('stagings-out-history')->name('stagings-out-history.')->group(function () {
    Route::get('/', [StagingOutHistoryController::class, 'index'])->name('index');
    Route::get('/data', [StagingOutHistoryController::class, 'data'])->name('data');
    Route::get('/export', [StagingOutHistoryController::class, 'export'])->name('export');
    Route::get('/{history}/detail', [StagingOutHistoryController::class, 'detail'])->name('detail');
});

Route::prefix('item-history')->name('item-history.')->group(function () {
    Route::get('/', [ItemHistoryController::class, 'index'])->name('index');
    Route::get('/data', [ItemHistoryController::class, 'data'])->name('data');
    Route::get('/{item}', [ItemHistoryController::class, 'show'])->name('show');
    Route::get('/{item}/timeline', [ItemHistoryController::class, 'timeline'])->name('timeline');
    Route::get('/{item}/export', [ItemHistoryController::class, 'export'])->name('export');
});
 