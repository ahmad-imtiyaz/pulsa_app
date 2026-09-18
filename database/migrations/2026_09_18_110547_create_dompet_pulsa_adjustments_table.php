<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dompet_pulsa_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dompet_pulsa_id')->constrained('dompet_pulsa')->cascadeOnDelete();
            $table->enum('jenis', ['tambah', 'kurang']);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dompet_pulsa_adjustments');
    }
};
