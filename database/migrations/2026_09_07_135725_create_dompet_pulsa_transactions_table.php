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
        Schema::create('dompet_pulsa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dompet_pulsa_id')->constrained('dompet_pulsa')->cascadeOnDelete();
            $table->enum('jenis', ['topup', 'penjualan']); // topup = penambahan saldo, penjualan = sales
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2); // For topup: jumlah topup. For penjualan: harga modal yang terpakai
            $table->decimal('harga_jual', 15, 2)->nullable(); // Only for penjualan
            $table->decimal('laba', 15, 2)->default(0); // Only for penjualan
            $table->string('nomor_hp')->nullable(); // For penjualan
            $table->string('provider')->nullable(); // For penjualan
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['dompet_pulsa_id', 'tanggal']);
            $table->index(['tanggal', 'jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dompet_pulsa_transactions');
    }
};
