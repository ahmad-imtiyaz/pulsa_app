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
        Schema::table('dompet_pulsa_transactions', function (Blueprint $table) {
            $table->dropColumn('nomor_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dompet_pulsa_transactions', function (Blueprint $table) {
            $table->string('nomor_hp')->nullable();
        });
    }
};
