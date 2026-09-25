<?php

use App\Models\DompetPulsa;
use App\Models\User;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const H1 = '2026-09-20';
const H2 = '2026-09-21';
const H3 = '2026-09-22';

function service(): DailySummaryService
{
    return app(DailySummaryService::class);
}

function buatDompet(User $user, float $saldoAwal = 1_000_000, string $kode = 'MOBO'): DompetPulsa
{
    return DompetPulsa::withoutGlobalScopes()->forceCreate([
        'user_id' => $user->id,
        'nama' => $kode,
        'kode' => $kode,
        'saldo_awal' => $saldoAwal,
        'saldo_delta' => 0,
        'is_active' => true,
    ]);
}

/** nominal = harga modal (saldo yang terpotong), harga_jual = yang dibayar customer */
function jual(DompetPulsa $dompet, string $tgl, float $modal, float $jual): void
{
    $dompet->transactions()->forceCreate([
        'jenis' => 'penjualan',
        'tanggal' => $tgl,
        'nominal' => $modal,
        'harga_jual' => $jual,
        'laba' => $jual - $modal,
    ]);
}

function topup(DompetPulsa $dompet, string $tgl, float $nominal): void
{
    $dompet->transactions()->forceCreate([
        'jenis' => 'topup',
        'tanggal' => $tgl,
        'nominal' => $nominal,
    ]);
}

function sesuaikan(DompetPulsa $dompet, string $tgl, string $jenis, float $nominal): void
{
    $dompet->adjustments()->forceCreate([
        'jenis' => $jenis,
        'tanggal' => $tgl,
        'nominal' => $nominal,
        'keterangan' => 'koreksi test',
    ]);
}

function hitung(DompetPulsa $dompet, string $tgl): array
{
    return service()->hitungDompet($dompet->fresh(), Carbon::parse($tgl));
}

function ringkasan(User $user, string $tgl)
{
    return service()->recalculateForDate(Carbon::parse($tgl), $user->id);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Skenario client: modal awal 1.000.000
    $this->dompet = buatDompet($this->user);

    // Hari 1: dua penjualan
    jual($this->dompet, H1, 9_800, 10_500);   // laba 700
    jual($this->dompet, H1, 19_700, 20_500);  // laba 800

    // Hari 2: satu penjualan + topup
    jual($this->dompet, H2, 5_000, 5_500);    // laba 500
    topup($this->dompet, H2, 100_000);
});

it('hari 1 sinkron: laba dan saldo sesuai', function () {
    $h = hitung($this->dompet, H1);

    expect($h['laba'])->toEqualWithDelta(1_500, 0.001)
        ->and($h['penjualan'])->toEqualWithDelta(29_500, 0.001)   // total harga modal
        ->and($h['harga_jual'])->toEqualWithDelta(31_000, 0.001)
        ->and($h['saldo_awal'])->toEqualWithDelta(1_000_000, 0.001)
        ->and($h['saldo_akhir'])->toEqualWithDelta(970_500, 0.001);
});

it('hari 2 sinkron: saldo terbawa dari hari 1, laba hanya milik hari 2', function () {
    $h = hitung($this->dompet, H2);

    expect($h['saldo_awal'])->toEqualWithDelta(970_500, 0.001)     // = saldo akhir hari 1
        ->and($h['laba'])->toEqualWithDelta(500, 0.001)            // bukan kumulatif, bukan negatif
        ->and($h['saldo_akhir'])->toEqualWithDelta(1_065_500, 0.001);
});

it('hari tanpa transaksi: laba 0 dan saldo tetap terbawa', function () {
    $h = hitung($this->dompet, H3);

    expect($h['laba'])->toEqualWithDelta(0, 0.001)
        ->and($h['saldo_awal'])->toEqualWithDelta(1_065_500, 0.001)
        ->and($h['saldo_akhir'])->toEqualWithDelta(1_065_500, 0.001);
});

it('modal awal dikunci: topup hanya mengubah saldo, bukan modal awal atau laba', function () {
    $h1 = hitung($this->dompet, H1);
    $h2 = hitung($this->dompet, H2);

    expect($h1['modal_awal'])->toEqualWithDelta(1_000_000, 0.001)
        ->and($h2['modal_awal'])->toEqualWithDelta(1_000_000, 0.001)
        ->and($this->dompet->fresh()->modal_awal_efektif)->toEqualWithDelta(1_000_000, 0.001);

    // Topup 100.000 di hari 2 tidak boleh menyentuh laba
    expect($h2['laba'])->toEqualWithDelta(500, 0.001);
});

it('recalculate hari lama tidak berubah setelah ada transaksi hari baru (idempotent)', function () {
    $sebelum = ringkasan($this->user, H1)->pulsa_laba;

    jual($this->dompet, H3, 1_000, 1_300);
    ringkasan($this->user, H3);

    $sesudah = ringkasan($this->user, H1)->pulsa_laba;

    expect((float) $sesudah)->toEqualWithDelta((float) $sebelum, 0.001)
        ->and((float) $sesudah)->toEqualWithDelta(1_500, 0.001);
});

it('input mundur (backdate) menggeser saldo hari berikutnya tapi bukan laba hari berikutnya', function () {
    ringkasan($this->user, H2); // sempat dihitung

    jual($this->dompet, H1, 1_000, 1_200); // ditambahkan belakangan untuk hari 1

    $h1 = hitung($this->dompet, H1);
    $h2 = hitung($this->dompet, H2);

    expect($h1['laba'])->toEqualWithDelta(1_700, 0.001)
        ->and($h2['saldo_awal'])->toEqualWithDelta(969_500, 0.001)
        ->and($h2['laba'])->toEqualWithDelta(500, 0.001);
});

it('penjualan di bawah modal tercatat sebagai rugi', function () {
    jual($this->dompet, H3, 10_000, 9_500);

    $h = hitung($this->dompet, H3);

    expect($h['laba'])->toEqualWithDelta(-500, 0.001);
});

it('penyesuaian saldo mengubah saldo tetapi tidak mengubah laba', function () {
    sesuaikan($this->dompet, H2, 'kurang', 2_000);

    $h = hitung($this->dompet, H2);

    expect($h['laba'])->toEqualWithDelta(500, 0.001)
        ->and($h['penyesuaian'])->toEqualWithDelta(-2_000, 0.001)
        ->and($h['saldo_akhir'])->toEqualWithDelta(1_063_500, 0.001);

    // Efeknya terbawa ke hari berikutnya
    expect(hitung($this->dompet, H3)['saldo_awal'])->toEqualWithDelta(1_063_500, 0.001);
});

it('identitas sinkron: total harga jual = modal terpakai + laba', function () {
    $totalJual = 0;
    $totalLaba = 0;
    foreach ([H1, H2] as $tgl) {
        $h = hitung($this->dompet, $tgl);
        $totalJual += $h['harga_jual'];
        $totalLaba += $h['laba'];
    }

    // modal terpakai dihitung dari saldo, bukan dari transaksi
    $modalTerpakai = 1_000_000 + 100_000 - hitung($this->dompet, H2)['saldo_akhir'];

    expect($modalTerpakai)->toEqualWithDelta(34_500, 0.001)
        ->and($totalJual)->toEqualWithDelta($modalTerpakai + $totalLaba, 0.001);
});

it('dashboard dan laporan memakai angka yang sama', function () {
    foreach ([H1, H2, H3] as $tgl) {
        $summary = ringkasan($this->user, $tgl);
        $dash = service()->getDashboardData(Carbon::parse($tgl), $this->user->id);

        expect((float) $summary->pulsa_saldo_akhir)
            ->toEqualWithDelta((float) $dash['total_saldo_pulsa'], 0.001)
            ->and((float) $summary->pulsa_laba)
            ->toEqualWithDelta((float) $dash['dompets']->sum('laba'), 0.001);
    }
});

it('laporan rentang = jumlah laba harian', function () {
    service()->recalculateRange(Carbon::parse(H1), Carbon::parse(H3), $this->user->id);

    $rows = service()->getCachedRange(Carbon::parse(H1), Carbon::parse(H3), $this->user->id);

    expect($rows)->toHaveCount(3)
        ->and((float) $rows->sum('pulsa_laba'))->toEqualWithDelta(2_000, 0.001);
});

it('beberapa dompet: laba dan saldo dijumlahkan per dompet', function () {
    $dana = buatDompet($this->user, 500_000, 'DANA');
    jual($dana, H1, 10_000, 10_400); // laba 400

    $summary = ringkasan($this->user, H1);

    expect((float) $summary->pulsa_laba)->toEqualWithDelta(1_900, 0.001)         // 1.500 + 400
        ->and((float) $summary->pulsa_saldo_akhir)->toEqualWithDelta(1_460_500, 0.001); // 970.500 + 490.000
});
