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
            // Hapus foreign key lama jika ada
            $table->dropForeign(['bahan_baku_id']);

            // Ubah kolom menjadi unsignedBigInteger
            $table->unsignedBigInteger('bahan_baku_id')->change();

            // Tambahkan foreign key baru
            $table->foreign('bahan_baku_id')->references('id')->on('bahan_baku')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname', function (Blueprint $table) {
            // Hapus foreign key baru
            $table->dropForeign(['bahan_baku_id']);

            // Kembalikan foreign key ke bentuk semula
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->change();
        });
    }
};
