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
        Schema::create('dompet_pulsa', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Mobo, Dompul, Digipos, Payafast, DANA, etc.
            $table->string('kode')->unique(); // Unique code for each wallet
            $table->decimal('saldo_awal', 15, 2)->default(0); // Opening balance
            $table->decimal('saldo_tersedia', 15, 2)->default(0); // Current available balance
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
        Schema::dropIfExists('dompet_pulsa');
    }
};
