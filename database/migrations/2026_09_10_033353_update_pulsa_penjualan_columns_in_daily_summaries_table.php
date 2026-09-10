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
        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->dropColumn(['pulsa_penjualan_modal', 'pulsa_penjualan_jual']);
            $table->decimal('pulsa_penjualan', 15, 2)->default(0)->after('pulsa_topup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->dropColumn(['pulsa_penjualan']);
            $table->decimal('pulsa_penjualan_modal', 15, 2)->default(0)->after('pulsa_topup');
            $table->decimal('pulsa_penjualan_jual', 15, 2)->default(0)->after('pulsa_penjualan_modal');
        });
    }
};
