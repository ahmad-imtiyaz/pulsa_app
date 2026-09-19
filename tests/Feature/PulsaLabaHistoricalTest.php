<?php

use App\Models\DailySummary;
use App\Models\DompetPulsa;
use App\Models\DompetPulsaTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menghitung laba pulsa berbeda per tanggal sesuai transaksi di tanggal itu, bukan saldo live hari ini', function () {
    $today = Carbon::today();
    $day1 = $today->copy()->subDays(4); // sebelum topup
    $day2 = $today->copy()->subDays(3); // sebelum topup
    $day3 = $today->copy()->subDays(2); // hari topup besar
    $day4 = $today->copy()->subDays(1); // setelah topup
    $day5 = $today->copy();             // hari ini, setelah topup

    $wallet1 = DompetPulsa::create([
        'nama' => 'TEST WALLET 1', 'kode' => 'TW1',
        'saldo_awal' => 1_000_000, 'sisa_saldo_awal' => 1_000_000,
        'saldo_tersedia' => 1_000_000, 'is_active' => true,
    ]);

    $wallet2 = DompetPulsa::create([
        'nama' => 'TEST WALLET 2', 'kode' => 'TW2',
        'saldo_awal' => 500_000, 'sisa_saldo_awal' => 500_000,
        'saldo_tersedia' => 500_000, 'is_active' => true,
    ]);

    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day1, 'nominal' => 100_000]);
    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet2->id, 'jenis' => 'penjualan', 'tanggal' => $day1, 'nominal' => 50_000]);

    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day2, 'nominal' => 80_000]);

    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'topup', 'tanggal' => $day3, 'nominal' => 200_000]);
    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet2->id, 'jenis' => 'penjualan', 'tanggal' => $day3, 'nominal' => 30_000]);

    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day4, 'nominal' => 60_000]);

    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet1->id, 'jenis' => 'penjualan', 'tanggal' => $day5, 'nominal' => 90_000]);
    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet2->id, 'jenis' => 'penjualan', 'tanggal' => $day5, 'nominal' => 40_000]);

    app(DailySummaryService::class)->recalculateRange($day1, $day5);

    $summaries = DailySummary::whereBetween('tanggal', [$day1->toDateString(), $day5->toDateString()])
        ->orderBy('tanggal')
        ->get()
        ->keyBy(fn ($s) => $s->tanggal->toDateString());

    // === Regression check utama ===
    // Kalau ini gagal (nilai day1 == nilai day5), berarti bug lama
    // (pakai saldo live/current, bukan saldo per tanggal) masih ada.
    expect((float) $summaries[$day1->toDateString()]->pulsa_laba)
        ->not->toEqual((float) $summaries[$day5->toDateString()]->pulsa_laba);

    // === Nilai eksak per tanggal ===
    // Dihitung manual dari rumus yang sudah di-fix:
    // labaRugi(tanggal) = saldo_akhir_kemarin - saldo_awal_tetap + topup(tanggal)
    expect((float) $summaries[$day1->toDateString()]->pulsa_laba)->toEqual(0.0);
    expect((float) $summaries[$day2->toDateString()]->pulsa_laba)->toEqual(-150_000.0);
    expect((float) $summaries[$day3->toDateString()]->pulsa_laba)->toEqual(-30_000.0);
    expect((float) $summaries[$day4->toDateString()]->pulsa_laba)->toEqual(-60_000.0);
    expect((float) $summaries[$day5->toDateString()]->pulsa_laba)->toEqual(-120_000.0);
});

it('tidak berubah lagi kalau recalculateForDate dipanggil ulang di kemudian hari (idempotent per tanggal)', function () {
    $date = Carbon::today()->subDays(2);

    $wallet = DompetPulsa::create([
        'nama' => 'TEST WALLET', 'kode' => 'TWX',
        'saldo_awal' => 300_000, 'sisa_saldo_awal' => 300_000,
        'saldo_tersedia' => 300_000, 'is_active' => true,
    ]);

    DompetPulsaTransaction::create(['dompet_pulsa_id' => $wallet->id, 'jenis' => 'penjualan', 'tanggal' => $date, 'nominal' => 20_000]);

    $service = app(DailySummaryService::class);
    $first = $service->recalculateForDate($date->copy());

    // Simulasikan waktu berjalan: transaksi baru dibuat di HARI LAIN (bukan $date)
    DompetPulsaTransaction::create([
        'dompet_pulsa_id' => $wallet->id, 'jenis' => 'topup',
        'tanggal' => Carbon::today(), 'nominal' => 5_000_000,
    ]);

    // Recalculate ulang untuk tanggal LAMA — hasilnya harus TETAP SAMA,
    // tidak boleh ikut berubah gara-gara ada topup besar di hari lain.
    $second = $service->recalculateForDate($date->copy());

    expect((float) $second->pulsa_laba)->toEqual((float) $first->pulsa_laba);
});
