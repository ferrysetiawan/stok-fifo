<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StokKeluar;
use Carbon\Carbon;

class InventoryStockService
{
    // public static function generateStokKeluarProdukDenganTarget(Inventory $inventory, Carbon $date)
    // {
    //     $startOfMonth    = $date->copy()->startOfMonth();
    //     $endOfMonth      = $date->copy()->endOfMonth();
    //     $endOfLastMonth  = $date->copy()->subMonthNoOverflow()->endOfMonth();

    //     $totalStokSaatIni = $inventory->stok;
    //     $targetStokAkhir  = $inventory->stok_akhir_bulan;
    //     $stokKeluarTotal  = $totalStokSaatIni - $targetStokAkhir;

    //     if ($stokKeluarTotal <= 0) {
    //         return;
    //     }

    //     // Ambil stokMasuk dari sisa bulan lalu & stok masuk bulan ini
    //     $stokMasuks = $inventory->bahanBakuMasuks()
    //         ->where(function ($q) use ($endOfLastMonth, $startOfMonth, $endOfMonth) {
    //             $q->whereDate('tanggal_masuk', '<=', $endOfLastMonth)
    //                 ->orWhereBetween('tanggal_masuk', [$startOfMonth, $endOfMonth]);
    //         })
    //         ->where('jumlah', '>', 0)
    //         ->orderBy('tanggal_masuk', 'asc')
    //         ->get();

    //     if ($stokMasuks->isEmpty()) {
    //         return;
    //     }

    //     $stokAvailable = $totalStokSaatIni;

    //     foreach ($stokMasuks as $stokMasuk) {
    //         $stokSisaDariMasukIni = $stokMasuk->jumlah;

    //         if ($stokSisaDariMasukIni <= 0) {
    //             continue;
    //         }

    //         while ($stokSisaDariMasukIni > 0 && $stokKeluarTotal > 0 && $stokAvailable > 0) {
    //             $maxRandom = min($stokSisaDariMasukIni, $stokKeluarTotal, $stokAvailable);
    //             $randomJumlah = rand(1, $maxRandom);

    //             $randomTanggal = Carbon::createFromFormat(
    //                 'Y-m-d',
    //                 $startOfMonth->format('Y-m-') . rand(
    //                     $startOfMonth->day,
    //                     $endOfMonth->day
    //                 )
    //             );

    //             StokKeluar::create([
    //                 'bahan_baku_id'  => $inventory->bahan_baku_id,
    //                 'jumlah'         => $randomJumlah,
    //                 'tanggal_keluar' => $randomTanggal->toDateString(),
    //             ]);

    //             $inventory->stok -= $randomJumlah;
    //             $stokAvailable   -= $randomJumlah;
    //             $stokMasuk->jumlah -= $randomJumlah;
    //             $stokSisaDariMasukIni -= $randomJumlah;
    //             $stokKeluarTotal -= $randomJumlah;
    //         }

    //         $stokMasuk->save();
    //         $inventory->save();

    //         if ($stokKeluarTotal <= 0 || $stokAvailable <= 0) {
    //             break;
    //         }
    //     }
    // }
    // public static function generateStokKeluarProdukDenganTarget(Inventory $inventory, Carbon $date)
    // {
    //     $startOfMonth   = $date->copy()->startOfMonth();
    //     $endOfMonth     = $date->copy()->endOfMonth();
    //     $endOfLastMonth = $date->copy()->subMonthNoOverflow()->endOfMonth();

    //     $totalStokSaatIni = $inventory->stok;
    //     $targetStokAkhir  = $inventory->stok_akhir_bulan;
    //     $stokKeluarTotal  = $totalStokSaatIni - $targetStokAkhir;

    //     if ($stokKeluarTotal <= 0) {
    //         return;
    //     }

    //     // Ambil stok masuk dari sisa bulan lalu + bulan ini, urut dari paling lama
    //     $stokMasuks = $inventory->bahanBakuMasuks()
    //         ->where(function ($q) use ($endOfLastMonth, $endOfMonth) {
    //             $q->whereDate('tanggal_masuk', '<=', $endOfMonth);
    //         })
    //         ->where('jumlah', '>', 0)
    //         ->orderBy('tanggal_masuk', 'asc')
    //         ->get();

    //     if ($stokMasuks->isEmpty()) {
    //         return;
    //     }

    //     foreach ($stokMasuks as $stokMasuk) {
    //         $stokSisaDariMasukIni = $stokMasuk->jumlah;

    //         if ($stokSisaDariMasukIni <= 0) {
    //             continue;
    //         }

    //         // Selama stok keluar target masih ada dan stok batch ini masih ada
    //         while ($stokKeluarTotal > 0 && $stokSisaDariMasukIni > 0) {
    //             $maxJumlah = min($stokSisaDariMasukIni, $stokKeluarTotal);

    //             // Ambil tanggal random antara tanggal_masuk dan endOfMonth
    //             $randomTanggal = Carbon::createFromFormat(
    //                 'Y-m-d',
    //                 Carbon::parse($stokMasuk->tanggal_masuk)->format('Y-m-') . rand(
    //                     Carbon::parse($stokMasuk->tanggal_masuk)->day,
    //                     Carbon::parse($endOfMonth)->day
    //                 )
    //             );

    //             // Simpan stok keluar
    //             StokKeluar::create([
    //                 'bahan_baku_id'      => $inventory->bahan_baku_id,
    //                 'bahan_baku_masuk_id' => $stokMasuk->id, // biar bisa tracing
    //                 'jumlah'             => $maxJumlah,
    //                 'tanggal_keluar'     => $randomTanggal->toDateString(),
    //             ]);

    //             $stokMasuk->jumlah -= $maxJumlah;
    //             $inventory->stok   -= $maxJumlah;
    //             $stokKeluarTotal   -= $maxJumlah;
    //             $stokSisaDariMasukIni -= $maxJumlah;

    //             $stokMasuk->save();
    //             $inventory->save();
    //         }

    //         if ($stokKeluarTotal <= 0) {
    //             break;
    //         }
    //     }
    // }

    // public static function generateStokKeluarProdukDenganTarget(Inventory $inventory, Carbon $date)
    // {
    //     $startOfMonth   = $date->copy()->startOfMonth();
    //     $endOfMonth     = $date->copy()->endOfMonth();

    //     $totalStokSaatIni = $inventory->stok;
    //     $targetStokAkhir  = $inventory->stok_akhir_bulan;
    //     $stokKeluarTotal  = $totalStokSaatIni - $targetStokAkhir;

    //     if ($stokKeluarTotal <= 0) {
    //         return;
    //     }

    //     // Cari stok masuk pertama di bulan ini
    //     $firstStokMasukThisMonth = $inventory->bahanBakuMasuks()
    //         ->whereBetween('tanggal_masuk', [$startOfMonth, $endOfMonth])
    //         ->orderBy('tanggal_masuk', 'asc')
    //         ->first();

    //     $firstTanggalMasukThisMonth = $firstStokMasukThisMonth
    //         ? Carbon::parse($firstStokMasukThisMonth->tanggal_masuk)
    //         : $endOfMonth->copy()->addDay(); // kalau gak ada, berarti sampai akhir bulan

    //     // Ambil stok masuk dari bulan lalu + bulan ini, urut dari paling lama
    //     $stokMasuks = $inventory->bahanBakuMasuks()
    //         ->whereDate('tanggal_masuk', '<=', $endOfMonth)
    //         ->where('jumlah', '>', 0)
    //         ->orderBy('tanggal_masuk', 'asc')
    //         ->get();

    //     if ($stokMasuks->isEmpty()) {
    //         return;
    //     }

    //     foreach ($stokMasuks as $stokMasuk) {
    //         $stokSisaDariMasukIni = $stokMasuk->jumlah;

    //         if ($stokSisaDariMasukIni <= 0) {
    //             continue;
    //         }

    //         while ($stokKeluarTotal > 0 && $stokSisaDariMasukIni > 0) {
    //             $maxJumlah = min($stokSisaDariMasukIni, $stokKeluarTotal);

    //             $tanggalMasukBatch = Carbon::parse($stokMasuk->tanggal_masuk);

    //             // Tentukan range random tanggal keluar
    //             if ($tanggalMasukBatch->lessThan($startOfMonth)) {
    //                 // Kalau batch dari bulan lalu → random antara awal bulan ini sampai sebelum stok masuk pertama bulan ini
    //                 $startDate = $startOfMonth;
    //                 $endDate = $firstTanggalMasukThisMonth->copy()->subDay();
    //             } else {
    //                 // Kalau batch dari bulan ini → random antara tanggal masuk sampai akhir bulan
    //                 $startDate = $tanggalMasukBatch;
    //                 $endDate = $endOfMonth;
    //             }

    //             // Kalau startDate lebih besar dari endDate, set ke startDate
    //             if ($startDate->greaterThan($endDate)) {
    //                 $randomTanggal = $startDate;
    //             } else {
    //                 $randomTanggal = Carbon::createFromFormat(
    //                     'Y-m-d',
    //                     $startDate->format('Y-m-') . rand($startDate->day, $endDate->day)
    //                 );
    //             }

    //             // Simpan stok keluar
    //             StokKeluar::create([
    //                 'bahan_baku_id'      => $inventory->bahan_baku_id,
    //                 'bahan_baku_masuk_id' => $stokMasuk->id,
    //                 'jumlah'             => $maxJumlah,
    //                 'tanggal_keluar'     => $randomTanggal->toDateString(),
    //             ]);

    //             $stokMasuk->jumlah   -= $maxJumlah;
    //             $inventory->stok     -= $maxJumlah;
    //             $stokKeluarTotal     -= $maxJumlah;
    //             $stokSisaDariMasukIni -= $maxJumlah;

    //             $stokMasuk->save();
    //             $inventory->save();
    //         }

    //         if ($stokKeluarTotal <= 0) {
    //             break;
    //         }
    //     }
    // }

    // public static function generateStokKeluarProdukDenganTarget(Inventory $inventory, Carbon $date)
    // {
    //     $startOfMonth   = $date->copy()->startOfMonth();
    //     $endOfMonth     = $date->copy()->endOfMonth();

    //     $totalStokSaatIni = $inventory->stok;
    //     $targetStokAkhir  = $inventory->stok_akhir_bulan;
    //     $stokKeluarTotal  = $totalStokSaatIni - $targetStokAkhir;

    //     if ($stokKeluarTotal <= 0) {
    //         return;
    //     }

    //     // Cari stok masuk pertama di bulan ini
    //     $firstStokMasukThisMonth = $inventory->bahanBakuMasuks()
    //         ->whereBetween('tanggal_masuk', [$startOfMonth, $endOfMonth])
    //         ->orderBy('tanggal_masuk', 'asc')
    //         ->first();

    //     $firstTanggalMasukThisMonth = $firstStokMasukThisMonth
    //         ? Carbon::parse($firstStokMasukThisMonth->tanggal_masuk)
    //         : $endOfMonth->copy()->addDay();

    //     // Ambil stok masuk dari bulan lalu + bulan ini, urut dari paling lama
    //     $stokMasuks = $inventory->bahanBakuMasuks()
    //         ->whereDate('tanggal_masuk', '<=', $endOfMonth)
    //         ->where('jumlah', '>', 0)
    //         ->orderBy('tanggal_masuk', 'asc')
    //         ->get();

    //     if ($stokMasuks->isEmpty()) {
    //         return;
    //     }

    //     foreach ($stokMasuks as $stokMasuk) {
    //         $stokSisaDariMasukIni = $stokMasuk->jumlah;

    //         if ($stokSisaDariMasukIni <= 0) {
    //             continue;
    //         }

    //         while ($stokKeluarTotal > 0 && $stokSisaDariMasukIni > 0) {
    //             $maxJumlah = min($stokSisaDariMasukIni, $stokKeluarTotal);

    //             $tanggalMasukBatch = Carbon::parse($stokMasuk->tanggal_masuk);

    //             // Tentukan range random tanggal keluar
    //             if ($tanggalMasukBatch->lessThan($startOfMonth)) {
    //                 $startDate = $startOfMonth;
    //                 $endDate = $firstTanggalMasukThisMonth->copy()->subDay();
    //             } else {
    //                 $startDate = $tanggalMasukBatch;
    //                 $endDate = $endOfMonth;
    //             }

    //             // Kalau startDate lebih besar dari endDate
    //             if ($startDate->greaterThan($endDate)) {
    //                 $randomTanggal = $startDate;
    //             } else {
    //                 // Buat array tanggal valid tanpa 31 Maret
    //                 $validDates = [];
    //                 $currentDate = $startDate->copy();

    //                 while ($currentDate->lte($endDate)) {
    //                     if (!($currentDate->format('m-d') === '04-01')) {
    //                         $validDates[] = $currentDate->copy();
    //                     }
    //                     $currentDate->addDay();
    //                 }

    //                 if (empty($validDates)) {
    //                     $randomTanggal = $startDate;
    //                 } else {
    //                     $randomTanggal = $validDates[array_rand($validDates)];
    //                 }
    //             }

    //             // Simpan stok keluar
    //             StokKeluar::create([
    //                 'bahan_baku_id'       => $inventory->bahan_baku_id,
    //                 'bahan_baku_masuk_id' => $stokMasuk->id,
    //                 'jumlah'              => $maxJumlah,
    //                 'tanggal_keluar'      => $randomTanggal->toDateString(),
    //             ]);

    //             $stokMasuk->jumlah   -= $maxJumlah;
    //             $inventory->stok     -= $maxJumlah;
    //             $stokKeluarTotal     -= $maxJumlah;
    //             $stokSisaDariMasukIni -= $maxJumlah;

    //             $stokMasuk->save();
    //             $inventory->save();
    //         }

    //         if ($stokKeluarTotal <= 0) {
    //             break;
    //         }
    //     }
    // }

    public static function generateStokKeluarProdukDenganTarget(Inventory $inventory, Carbon $date)
    {
        $startOfMonth   = $date->copy()->startOfMonth();
        $endOfMonth     = $date->copy()->endOfMonth();
        $today          = Carbon::now()->startOfDay();

        $totalStokSaatIni = $inventory->stok;
        $targetStokAkhir  = $inventory->stok_akhir_bulan;
        $stokKeluarTotal  = $totalStokSaatIni - $targetStokAkhir;

        if ($stokKeluarTotal <= 0) {
            return;
        }

        $firstStokMasukThisMonth = $inventory->bahanBakuMasuks()
            ->whereBetween('tanggal_masuk', [$startOfMonth, $endOfMonth])
            ->orderBy('tanggal_masuk', 'asc')
            ->first();

        $firstTanggalMasukThisMonth = $firstStokMasukThisMonth
            ? Carbon::parse($firstStokMasukThisMonth->tanggal_masuk)
            : $endOfMonth->copy()->addDay();

        $stokMasuks = $inventory->bahanBakuMasuks()
            ->whereDate('tanggal_masuk', '<=', $endOfMonth)
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        if ($stokMasuks->isEmpty()) {
            return;
        }

        foreach ($stokMasuks as $stokMasuk) {
            $stokSisaDariMasukIni = $stokMasuk->jumlah;

            if ($stokSisaDariMasukIni <= 0) {
                continue;
            }

            while ($stokKeluarTotal > 0 && $stokSisaDariMasukIni > 0) {
                $maxJumlah = min($stokSisaDariMasukIni, $stokKeluarTotal);

                $tanggalMasukBatch = Carbon::parse($stokMasuk->tanggal_masuk);

                if ($tanggalMasukBatch->lessThan($startOfMonth)) {
                    $startDate = $startOfMonth;
                    $endDate = $firstTanggalMasukThisMonth->copy()->subDay();
                } else {
                    $startDate = $tanggalMasukBatch;
                    $endDate = $endOfMonth;
                }

                if ($endDate->greaterThan($today)) {
                    $endDate = $today;
                }

                if ($startDate->greaterThan($endDate)) {
                    $randomTanggal = $startDate;
                } else {
                    $validDates = [];
                    $currentDate = $startDate->copy();

                    while ($currentDate->lte($endDate)) {
                        $validDates[] = $currentDate->copy();
                        $currentDate->addDay();
                    }

                    $randomTanggal = empty($validDates)
                        ? $startDate
                        : $validDates[array_rand($validDates)];
                }

                StokKeluar::create([
                    'bahan_baku_id'       => $inventory->bahan_baku_id,
                    'bahan_baku_masuk_id' => $stokMasuk->id,
                    'jumlah'              => $maxJumlah,
                    'tanggal_keluar'      => $randomTanggal->toDateString(),
                ]);

                $stokMasuk->jumlah   -= $maxJumlah;
                $inventory->stok     -= $maxJumlah;
                $stokKeluarTotal     -= $maxJumlah;
                $stokSisaDariMasukIni -= $maxJumlah;

                $stokMasuk->save();
                $inventory->save();
            }

            if ($stokKeluarTotal <= 0) {
                break;
            }
        }
    }
}
