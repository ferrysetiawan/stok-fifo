<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Models\StockOpname;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessStockOpname implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $opnames;

    public function __construct(array $opnames)
    {
        $this->opnames = $opnames;
    }

    public function handle(): void
    {
        $tanggalOpname = Carbon::now();
        $startOfMonth = $tanggalOpname->copy()->startOfMonth();
        $endOfMonth = $tanggalOpname->copy()->endOfMonth();

        DB::transaction(function () use ($startOfMonth, $endOfMonth, $tanggalOpname) {
            foreach ($this->opnames as $opname) {
                $inventory = Inventory::where('bahan_baku_id', $opname['bahan_baku_id'])->first();

                $stokAwal = $inventory->stok_awal_bulan;

                $penerimaan = StokMasuk::where('bahan_baku_id', $opname['bahan_baku_id'])
                    ->whereBetween('tanggal_masuk', [$startOfMonth, $endOfMonth])
                    ->sum('qty');

                $pengeluaran = StokKeluar::where('bahan_baku_id', $opname['bahan_baku_id'])
                    ->whereBetween('tanggal_keluar', [$startOfMonth, $endOfMonth])
                    ->sum('jumlah');

                $stokAkhir = $stokAwal + $penerimaan - $pengeluaran;
                $stokFisik = $opname['stok_fisik'];
                $selisih = $stokFisik - $stokAkhir;

                StockOpname::create([
                    'bahan_baku_id' => $opname['bahan_baku_id'],
                    'stok_awal' => $stokAwal,
                    'penerimaan' => $penerimaan,
                    'pengeluaran' => $pengeluaran,
                    'stok_akhir' => $stokAkhir,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'tanggal_opname' => $tanggalOpname,
                ]);

                $inventory->stok = $stokFisik;
                $inventory->save();

                $this->adjustStockMasuk($opname['bahan_baku_id'], $stokFisik);
            }
        });
    }

    private function adjustStockMasuk($bahanBakuId, $stokFisik)
    {
        $stokMasukItems = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        $totalStokMasuk = $stokMasukItems->sum('jumlah');
        $selisih = $stokFisik - $totalStokMasuk;

        Log::info("Starting adjustment for bahan_baku_id: $bahanBakuId with selisih: $selisih");

        foreach ($stokMasukItems as $stokMasuk) {
            if ($selisih == 0) {
                break;
            }

            Log::info("Processing StokMasuk ID: $stokMasuk->id, Current Jumlah: $stokMasuk->jumlah, Selisih: $selisih");

            if ($selisih < 0) { // Decrease stock
                $decreaseAmount = min($stokMasuk->jumlah, abs($selisih));
                $stokMasuk->jumlah -= $decreaseAmount;
                $selisih += $decreaseAmount;
            } elseif ($selisih > 0) { // Increase stock
                $increaseAmount = min($stokMasuk->jumlah, $selisih);
                $stokMasuk->jumlah += $increaseAmount;
                $selisih -= $increaseAmount;
            }

            $stokMasuk->save();

            Log::info("After adjustment, StokMasuk ID: $stokMasuk->id, Updated Jumlah: $stokMasuk->jumlah");
        }

        // Jika selisih lebih dari 0, buat stok masuk baru
        if ($selisih > 0) {
            Log::info("Creating new StokMasuk for bahan_baku_id: $bahanBakuId with jumlah: $selisih");
            StokMasuk::create([
                'bahan_baku_id' => $bahanBakuId,
                'jumlah' => $selisih,
                'qty' => $selisih,
                'tanggal_masuk' => Carbon::now(), // Atur tanggal sesuai kebutuhan Anda
            ]);
        }

        // Jika selisih kurang dari 0, buat stok keluar baru
        if ($selisih < 0) {
            Log::info("Creating new StokKeluar for bahan_baku_id: $bahanBakuId with jumlah: " . abs($selisih));
            StokKeluar::create([
                'bahan_baku_id' => $bahanBakuId,
                'jumlah' => abs($selisih),
                'tanggal_keluar' => Carbon::now(), // Atur tanggal sesuai kebutuhan Anda
            ]);
        }

        Log::info("Final Selisih: $selisih");
    }

}
