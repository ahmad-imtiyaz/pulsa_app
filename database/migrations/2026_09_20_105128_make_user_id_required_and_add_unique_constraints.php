<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['user_id', 'kode']);
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['user_id', 'kode']);
        });

        Schema::table('aksesoris', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['user_id', 'sku']);
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'kode']);
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'kode']);
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('aksesoris', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'sku']);
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'tanggal']);
            $table->foreignId('user_id')->nullable()->change();
        });
    }
};
