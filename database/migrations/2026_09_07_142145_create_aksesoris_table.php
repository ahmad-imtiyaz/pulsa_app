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
        Schema::create('aksesoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('sku')->unique(); // SKU / Kode barang
            $table->string('kategori')->nullable(); // Charger, Kabel, Headset, etc.
            $table->decimal('harga_modal', 15, 2)->default(0); // Current cost price
            $table->decimal('harga_jual', 15, 2)->default(0); // Current selling price
            $table->integer('stok')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksesoris');
    }
};
