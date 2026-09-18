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
            $table->decimal('saldo_override', 20, 2)->nullable()->after('saldo_tersedia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->decimal('saldo_override', 20, 2)->nullable()->after('saldo_tersedia');
        });
    }
};
