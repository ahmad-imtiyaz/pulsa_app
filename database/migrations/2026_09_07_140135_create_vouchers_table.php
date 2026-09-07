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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->string('jenis'); // e.g., 'Game', 'PLN', 'Pulsa', etc.
            $table->decimal('nilai', 15, 2); // Face value / nominal voucher
            $table->decimal('harga_modal', 15, 2); // Cost price
            $table->decimal('harga_jual', 15, 2); // Selling price
            $table->integer('stok')->default(0);
            $table->date('tanggal_berlaku')->nullable(); // Expiry date
            $table->enum('status', ['aktif', 'nonaktif', 'habis'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
