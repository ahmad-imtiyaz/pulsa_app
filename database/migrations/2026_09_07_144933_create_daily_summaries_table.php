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
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();

            // Pulsa summary
            $table->decimal('pulsa_saldo_awal', 15, 2)->default(0);
            $table->decimal('pulsa_topup', 15, 2)->default(0);
            $table->decimal('pulsa_penjualan_modal', 15, 2)->default(0);
            $table->decimal('pulsa_penjualan_jual', 15, 2)->default(0);
            $table->decimal('pulsa_saldo_akhir', 15, 2)->default(0);
            $table->decimal('pulsa_laba', 15, 2)->default(0);

            // Voucher summary
            $table->decimal('voucher_penjualan_modal', 15, 2)->default(0);
            $table->decimal('voucher_penjualan_jual', 15, 2)->default(0);
            $table->decimal('voucher_laba', 15, 2)->default(0);

            // Aksesoris summary
            $table->decimal('aksesoris_penjualan_modal', 15, 2)->default(0);
            $table->decimal('aksesoris_penjualan_jual', 15, 2)->default(0);
            $table->decimal('aksesoris_laba', 15, 2)->default(0);

            // Total laba kotor
            $table->decimal('total_laba_kotor', 15, 2)->default(0);

            // Pengeluaran
            $table->decimal('pengeluaran_operasional', 15, 2)->default(0);
            $table->decimal('pengeluaran_gaji', 15, 2)->default(0);
            $table->decimal('pengeluaran_pribadi', 15, 2)->default(0);
            $table->decimal('total_pengeluaran', 15, 2)->default(0);

            // Sisa laba
            $table->decimal('sisa_laba', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
