<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->float('stok_awal')->change();
            $table->float('penerimaan')->change();
            $table->float('pengeluaran')->change();
            $table->float('stok_akhir')->change();
            $table->float('stok_fisik')->change();
            $table->float('selisih')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->integer('stok_awal')->change();
            $table->integer('penerimaan')->change();
            $table->integer('pengeluaran')->change();
            $table->integer('stok_akhir')->change();
            $table->integer('stok_fisik')->change();
            $table->integer('selisih')->change();
        });
    }
};
