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
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->decimal('sisa_saldo_awal', 15, 2)->after('saldo_awal')->default(0)->comment('Sisa Saldo Saat Ini - input manual di awal, tidak berubah dari transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->dropColumn('sisa_saldo_awal');
        });
    }
};
