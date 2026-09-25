<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dompet_pulsa_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('dompet_pulsa_transactions', 'harga_jual')) {
                $table->decimal('harga_jual', 15, 2)->nullable()->after('nominal');
            }
            if (! Schema::hasColumn('dompet_pulsa_transactions', 'laba')) {
                $table->decimal('laba', 15, 2)->nullable()->after('harga_jual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dompet_pulsa_transactions', function (Blueprint $table) {
            foreach (['laba', 'harga_jual'] as $kolom) {
                if (Schema::hasColumn('dompet_pulsa_transactions', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
