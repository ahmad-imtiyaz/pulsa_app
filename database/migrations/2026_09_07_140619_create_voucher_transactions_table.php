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
        Schema::create('voucher_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->date('tanggal');
            $table->integer('jumlah'); // Quantity sold
            $table->decimal('harga_modal', 15, 2); // Cost per unit at time of sale
            $table->decimal('harga_jual', 15, 2); // Selling price per unit at time of sale
            $table->decimal('total_modal', 15, 2); // harga_modal * jumlah
            $table->decimal('total_penjualan', 15, 2); // harga_jual * jumlah
            $table->decimal('laba', 15, 2); // total_penjualan - total_modal
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['voucher_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_transactions');
    }
};
