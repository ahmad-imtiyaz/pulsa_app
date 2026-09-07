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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['operasional', 'gaji', 'pribadi']);
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->string('keterangan')->nullable(); // For operasional: detail pengeluaran. For gaji: nama karyawan. For pribadi: keperluan
            $table->string('karyawan_nama')->nullable(); // Only for gaji
            $table->timestamps();

            $table->index(['kategori', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
