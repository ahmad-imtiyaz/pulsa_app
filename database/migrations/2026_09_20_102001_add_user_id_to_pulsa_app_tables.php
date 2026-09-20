<?php
// database/migrations/2026_09_20_000001_add_user_id_to_pulsa_app_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['kode']);
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['kode']);
        });

        Schema::table('aksesoris', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['sku']);
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('dompet_pulsa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->unique('kode');
        });
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->unique('kode');
        });
        Schema::table('aksesoris', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->unique('sku');
        });
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->unique('tanggal');
        });
    }
};
