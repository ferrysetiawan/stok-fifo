<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Inventory;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $kategoryValue = Kategori::count();
        $bahanBakuValue = BahanBaku::count();
        $inventories = Inventory::with('bahanBaku', 'bahanBakuMasuks', 'stokKeluar')->get();
        $grandTotalValue = 0; // Initialize grand total value for stock
        $grandUsedValue = 0; // Initialize grand total value for used stock
        $grandInitialStockValue = 0;
        $costBulanan = 0;

        $currentMonth = Carbon::now()->month; // Get the current month
        $currentYear = Carbon::now()->year; // Get the current year

        foreach ($inventories as $inventory) {
            $totalValue = 0;
            $usedValue = 0;
            $initialStockValue = 0;

            foreach ($inventory->bahanBakuMasuks as $bahanBakuMasuk) {
                $tanggalMasuk = Carbon::parse($bahanBakuMasuk->tanggal_masuk);
                if ($bahanBakuMasuk->qty > 0 && $tanggalMasuk->month == $currentMonth && $tanggalMasuk->year == $currentYear) {

                    $harga = $inventory->bahanBaku->harga; // Get the price of the raw material
                    $totalValue += $bahanBakuMasuk->qty * $harga; // Calculate total value for each inventory
                }
            }

            if ($inventory->stok_awal_bulan > 0) {
                $harga = $inventory->bahanBaku->harga; // Get the price of the raw material
                $initialStockValue = $inventory->stok_awal_bulan * $harga; // Calculate initial stock value
            }

            if ($inventory->stok > 0) {
                $harga = $inventory->bahanBaku->harga; // Get the price of the raw material
                $usedValue = $inventory->stok * $harga; // Calculate initial stock value
            }

            $grandTotalValue += $totalValue; // Add to the grand total value for stock
            $grandUsedValue += $usedValue; // Add to the grand total value for used stock
            $grandInitialStockValue += $initialStockValue;
            $costBulanan = $grandTotalValue + ($grandInitialStockValue -  $grandUsedValue);
        }

        return view('dashboard', [
            'grandTotalValue' => $grandTotalValue,
            'grandUsedValue' => $grandUsedValue,
            'kategoryValue' => $kategoryValue,
            'grandInitialStockValue' => $grandInitialStockValue,
            'bahanBakuValue' => $bahanBakuValue,
            'costBulanan' => $costBulanan
        ]);
    }
}
