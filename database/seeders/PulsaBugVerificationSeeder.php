<?php

namespace Database\Seeders;

use App\Models\DompetPulsa;
use App\Models\DompetPulsaTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder KHUSUS UNTUK TESTING LOKAL — bukan untuk data produksi.
 *
 * Skenario: 2 dompet test dengan transaksi tersebar di 5 hari terakhir.
 * Yang penting: TOPUP BESAR (200.000) cuma terjadi di 1 hari (hari ke-3),
 * bukan di hari-hari lain.
 *
 * - Kalau bug LAMA masih ada: nilai "Laba Pulsa" di hari-hari SEBELUM
 *   topup itu akan ikut "terpengaruh" oleh topup yang sebenarnya belum
 *   terjadi di tanggal itu (karena kode lama pakai saldo live/hari ini,
 *   bukan saldo per tanggal historis).
 * - Kalau sudah FIX: laba di hari 1 & 2 (sebelum topup) harus berbeda
 *   dan TIDAK ikut kebagian efek topup di hari ke-3.
 *
 * Cara pakai:
 *   php artisan db:seed --class=PulsaBugVerificationSeeder
 *   lalu buka /laporan di browser lokal, filter sesuai tanggal yang
 *   dicetak di terminal setelah seeder selesai jalan.
 *
 * Cara hapus data test setelah selesai:
 *   php artisan tinker
 *   >>> App\Models\DompetPulsa::whereIn('kode', ['TESTWALLET1', 'TESTWALLET2'])
 *          ->get()->each(function ($d) { $d->transactions()->delete(); $d->delete(); });
 */
class PulsaBugVerificationSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data test lama dulu biar seeder ini aman dijalankan berulang kali
        DompetPulsa::whereIn('kode', ['TESTWALLET1', 'TESTWALLET2'])->get()->each(function ($d) {
            $d->transactions()->delete();
            $d->adjustments()->delete();
            $d->delete();
        });

        $today = Carbon::today();
        $day1 = $today->copy()->subDays(4); // paling lama, sebelum topup
        $day2 = $today->copy()->subDays(3); // masih sebelum topup
        $day3 = $today->copy()->subDays(2); // HARI TOPUP BESAR
        $day4 = $today->copy()->subDays(1); // setelah topup
        $day5 = $today->copy();             // hari ini, setelah topup

        $wallet1 = DompetPulsa::create([
            'nama' => 'TEST WALLET 1',
            'kode' => 'TESTWALLET1',
            'saldo_awal' => 1_000_000,
            'sisa_saldo_awal' => 1_000_000,
            'saldo_tersedia' => 1_000_000,
            'is_active' => true,
        ]);

        $wallet2 = DompetPulsa::create([
            'nama' => 'TEST WALLET 2',
            'kode' => 'TESTWALLET2',
            'saldo_awal' => 500_000,
            'sisa_saldo_awal' => 500_000,
            'saldo_tersedia' => 500_000,
            'is_active' => true,
        ]);

        // Hari 1: penjualan biasa, BELUM ada topup sama sekali
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day1, 'nominal' => 100_000]);
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet2->id, 'jenis' => 'penjualan', 'tanggal' => $day1, 'nominal' => 50_000]);

        // Hari 2: penjualan biasa lagi
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day2, 'nominal' => 80_000]);

        // Hari 3: TOPUP BESAR terjadi di sini
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'topup', 'tanggal' => $day3, 'nominal' => 200_000]);
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet2->id, 'jenis' => 'penjualan', 'tanggal' => $day3, 'nominal' => 30_000]);

        // Hari 4
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day4, 'nominal' => 60_000]);

        // Hari 5 (hari ini)
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day5, 'nominal' => 90_000]);
        DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet2->id, 'jenis' => 'penjualan', 'tanggal' => $day5, 'nominal' => 40_000]);

        app(DailySummaryService::class)->recalculateRange($day1, $day5);

        $this->command->info('');
        $this->command->info('=== Data test berhasil dibuat ===');
        $this->command->info("Rentang tanggal: {$day1->toDateString()} s/d {$day5->toDateString()}");
        $this->command->info("Topup besar (200.000) terjadi di tanggal: {$day3->toDateString()}");
        $this->command->info('');
        $this->command->warn('CARA CEK:');
        $this->command->line("1. Buka /laporan di browser lokal");
        $this->command->line("2. Filter dari {$day1->toDateString()} sampai {$day5->toDateString()}");
        $this->command->line('3. Lihat kolom "Laba Pulsa" per baris tanggal di tabel "Ringkasan Harian"');
        $this->command->line('');
        $this->command->line('   Nilai yang BENAR (setelah fix) untuk tiap tanggal:');
        $this->command->line("   {$day1->toDateString()} -> Rp 0");
        $this->command->line("   {$day2->toDateString()} -> -Rp 150.000");
        $this->command->line("   {$day3->toDateString()} -> -Rp 30.000");
        $this->command->line("   {$day4->toDateString()} -> -Rp 60.000");
        $this->command->line("   {$day5->toDateString()} -> -Rp 120.000");
        $this->command->line('');
        $this->command->error('   Kalau semua tanggal nilainya SAMA PERSIS satu sama lain -> bug masih ada.');
        $this->command->info('   (Nilai negatif di sini normal — ini cuma data dummy, bukan cerminan bisnis riil.)');
    }
}
