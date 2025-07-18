<?php

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryHistoryController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StokKeluarController;
use App\Http\Controllers\StokMasukController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('auth.login');
// });

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', KategoriController::class);
    Route::resource('bahan_baku', BahanBakuController::class);
    // import bahan baku
    Route::post('/import', [BahanBakuController::class, 'import'])->name('import');

    Route::resource('stok_masuk', StokMasukController::class);
    Route::get('/stok-masuk/export', [StokMasukController::class, 'exportExcel'])->name('stok_masuk.export');
    Route::post('bahan/store', [StokMasukController::class, 'bahanBakuStore'])->name('bahan.store');
    // ajax bahan baku search
    Route::get('/stok/bahanbaku', [StokMasukController::class, 'bahanBaku'])->name('stok_masuk.bahanbaku');

    Route::post('/import-stok-masuk', [StokMasukController::class, 'import'])->name('stok-masuk.import');


    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/per-produk', [InventoryController::class, 'perProduk'])->name('inventory.perProduk');
    Route::get('/per-produk/{id}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/simple-inventory/export-pdf', [InventoryController::class, 'exportSimpleInventoryPDF'])->name('inventory.simple-export-pdf');
    Route::get('/simple-inventory/export-excel', [InventoryController::class, 'exportExcel'])->name('inventory.simple-export-excel');
    Route::get('detail_inventory/{category_id?}', [InventoryController::class, 'indexs'])->name('detail_inventory.index');

    Route::post('inventory/update-stok-awal-bulan', [InventoryController::class, 'updateStokAwalBulan'])->name('inventory.update-stok-awal-bulan');

    Route::get('inventory-history', [InventoryHistoryController::class, 'index'])->name('inventory.history');
    Route::get('/inventory-history/export-pdf', [InventoryHistoryController::class, 'exportPDF'])->name('inventory.export-pdf');

    Route::post('/inventory/update-stok-akhir-bulan', [InventoryController::class, 'updateStokAkhirBulan'])->name('inventory.update-stok-akhir-bulan');
    Route::get('/inventory/download-all-pdf', [InventoryController::class, 'downloadAllPdf'])->name('inventory.downloadAllPdf');

    Route::get('/inventory/set-stok-akhir', [InventoryController::class, 'setStokAkhir'])->name('inventory.setStokAkhir');
    Route::post('/inventory/store-stok-akhir', [InventoryController::class, 'storeStokAkhir'])->name('inventory.storeStokAkhir');



    Route::get('/stok/export', [LaporanController::class, 'exportForm'])->name('stok.export.form');
    Route::get('/stok/export-excel', [LaporanController::class, 'exportExcel'])->name('stok.export.excel');

    Route::get('/stok-pembelian/export', [LaporanController::class, 'exportPembelianForm'])->name('stok.exportpembelian.form');
    Route::get('/stok-pembelian/export-excel', [LaporanController::class, 'exportStokMasuk'])->name('stok.exportpembelian.excel');


    Route::resource('stok_keluar', StokKeluarController::class);
    Route::get('/stok-keluar/export', [StokKeluarController::class, 'exportExcel'])->name('stok_keluar.export');

    Route::get('stock-opname', [StockOpnameController::class, 'index'])->name('stockOpname.index');
    Route::get('stock-opname/create', [StockOpnameController::class, 'create'])->name('stockOpname.create');
    Route::post('stock-opname/store', [StockOpnameController::class, 'store'])->name('stockOpname.store');
    Route::get('stock-opname/{id}/edit', [StockOpnameController::class, 'edit'])->name('stockOpname.edit');
    Route::put('stock-opname/update/{id}', [StockOpnameController::class, 'update'])->name('stockOpname.update');
    Route::get('stock-opname/format', [StockOpnameController::class, 'format'])->name('stockOpname.format');

    Route::get('/laporan-stok-masuk', [LaporanController::class, 'laporanStokMasuk'])->name('laporan.stokMasuk');
    Route::get('/laporan-stok-keluar', [LaporanController::class, 'laporanStokKeluar'])->name('laporan.stokKeluar');
});

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__ . '/auth.php';
