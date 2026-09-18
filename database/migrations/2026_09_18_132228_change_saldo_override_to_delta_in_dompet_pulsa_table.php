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
            $table->renameColumn('saldo_override', 'saldo_delta');
            $table->decimal('saldo_delta', 20, 2)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->renameColumn('saldo_override', 'saldo_delta');
            $table->decimal('saldo_delta', 20, 2)->nullable()->default(0)->change();
        });
    }
};
