<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->lockUserId('dompet_pulsa', ['user_id', 'kode']);
        $this->lockUserId('vouchers', ['user_id', 'kode']);
        $this->lockUserId('aksesoris', ['user_id', 'sku']);
        $this->lockUserId('pengeluaran', null);
        $this->lockUserId('daily_summaries', ['user_id', 'tanggal']);
    }

    public function down(): void
    {
        //
    }

    private function lockUserId(string $table, ?array $uniqueColumns): void
    {
        $columns = Schema::getColumns($table);
        $userIdColumn = collect($columns)->firstWhere('name', 'user_id');

        if ($userIdColumn && $userIdColumn['nullable']) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('user_id')->nullable(false)->change();
            });
        }

        if ($uniqueColumns && ! Schema::hasIndex($table, $uniqueColumns, 'unique')) {
            Schema::table($table, function (Blueprint $t) use ($uniqueColumns) {
                $t->unique($uniqueColumns);
            });
        }
    }
};
