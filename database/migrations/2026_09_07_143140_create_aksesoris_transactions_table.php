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
        Schema::create('aksesoris_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aksesoris_id')->constrained('aksesoris')->cascadeOnDelete();
            $table->enum('jenis', ['pembelian', 'penjualan']);
            $table->date('tanggal');
            $table->integer('jumlah');
            $table->decimal('harga_modal', 15, 2);
            $table->decimal('harga_jual', 15, 2)->nullable(); // Only for penjualan
            $table->decimal('total_modal', 15, 2);
            $table->decimal('total_penjualan', 15, 2)->nullable(); // Only for penjualan
            $table->decimal('laba', 15, 2)->default(0); // Only for penjualan
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['aksesoris_id', 'tanggal']);
            $table->index(['tanggal', 'jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksesoris_transactions');
    }
};
