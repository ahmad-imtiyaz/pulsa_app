<?php

namespace App\Services;

use App\Models\Aksesoris;
use App\Models\AksesorisTransaction;
use App\Models\DailySummary;
use App\Models\DompetPulsa;
use App\Models\Pengeluaran;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    public function recalculateForDate(Carbon $date, int $userId): DailySummary
    {
        return DB::transaction(function () use ($date, $userId) {
            $summary = DailySummary::withoutGlobalScopes()->firstOrCreate([
                'tanggal' => $date->toDateString(),
                'user_id' => $userId,
            ]);

            $this->calculatePulsaSummary($summary, $date, $userId);
            $this->calculateVoucherSummary($summary, $date, $userId);
            $this->calculateAksesorisSummary($summary, $date, $userId);

            $summary->total_laba_kotor =
                $summary->pulsa_laba +
                $summary->voucher_laba +
                $summary->aksesoris_laba;

            $this->calculatePengeluaranSummary($summary, $date, $userId);

            $summary->total_pengeluaran =
                $summary->pengeluaran_operasional +
                $summary->pengeluaran_gaji +
                $summary->pengeluaran_pribadi;

            $summary->sisa_laba = $summary->total_laba_kotor - $summary->total_pengeluaran;

            $summary->save();

            return $summary->fresh();
        });
    }

    private function calculatePulsaSummary(DailySummary $summary, Carbon $date, int $userId): void
    {
        $dompets = DompetPulsa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->with('adjustments')
            ->get();

        $totalSaldoAwal = 0;
        $totalTopup = 0;
        $totalPenjualan = 0;
        $totalLabaRugi = 0;

        foreach ($dompets as $dompet) {
            $saldoAwal = $this->getSaldoAwalDompet($dompet, $date, $userId);
            $totalSaldoAwal += $saldoAwal;

            $topup = $dompet->topupTransactions()->whereDate('tanggal', $date)->sum('nominal');
            $totalTopup += $topup;

            $penjualanHariIni = $dompet->penjualanTransactions()->whereDate('tanggal', $date)->sum('nominal');
            $totalPenjualan += $penjualanHariIni;

            $modalAwal = $dompet->saldo_awal;
            $selisih = $modalAwal - $penjualanHariIni;

            $adjustmentSampaiTanggal = $dompet->adjustments
                ->filter(fn ($a) => Carbon::parse($a->tanggal)->lte($date))
                ->sum(fn ($a) => $a->jenis === 'tambah' ? $a->nominal : -$a->nominal);

            $delta = $date->isToday() ? ($dompet->saldo_delta ?? 0) : 0;

            $sisaSaldoSaatIni = $saldoAwal + $topup - $penjualanHariIni + $adjustmentSampaiTanggal + $delta;

            $labaRugi = $sisaSaldoSaatIni - $selisih;
            $totalLabaRugi += $labaRugi;
        }

        $summary->pulsa_saldo_awal = $totalSaldoAwal;
        $summary->pulsa_topup = $totalTopup;
        $summary->pulsa_penjualan = $totalPenjualan;
        $summary->pulsa_saldo_akhir = $totalSaldoAwal + $totalTopup - $totalPenjualan;
        $summary->pulsa_laba = $totalLabaRugi;
    }

    public function getSaldoAwalDompet(DompetPulsa $dompet, Carbon $date, int $userId): float
    {
        $kemarin = $date->copy()->subDay();
        $summaryKemarin = DailySummary::withoutGlobalScopes()
            ->where('tanggal', $kemarin->toDateString())
            ->where('user_id', $userId)
            ->first();

        if ($summaryKemarin) {
            $saldoAkhirKemarin = $dompet->saldo_awal
                + $dompet->topupTransactions()->whereDate('tanggal', '<=', $kemarin)->sum('nominal')
                - $dompet->penjualanTransactions()->whereDate('tanggal', '<=', $kemarin)->sum('nominal');

            return max(0, $saldoAkhirKemarin);
        }

        return $dompet->saldo_awal
            + $dompet->topupTransactions()->whereDate('tanggal', '<', $date)->sum('nominal')
            - $dompet->penjualanTransactions()->whereDate('tanggal', '<', $date)->sum('nominal');
    }

    private function calculateVoucherSummary(DailySummary $summary, Carbon $date, int $userId): void
    {
        $transactions = VoucherTransaction::whereDate('tanggal', $date)
            ->whereHas('voucher', function ($q) use ($userId) {
                $q->withoutGlobalScopes()->where('user_id', $userId);
            })
            ->get();

        $summary->voucher_penjualan_modal = $transactions->sum('total_modal');
        $summary->voucher_penjualan_jual = $transactions->sum('total_penjualan');
        $summary->voucher_laba = $transactions->sum('laba');
    }

    private function calculateAksesorisSummary(DailySummary $summary, Carbon $date, int $userId): void
    {
        $transactions = AksesorisTransaction::where('jenis', 'penjualan')
            ->whereDate('tanggal', $date)
            ->whereHas('aksesoris', function ($q) use ($userId) {
                $q->withoutGlobalScopes()->where('user_id', $userId);
            })
            ->get();

        $summary->aksesoris_penjualan_modal = $transactions->sum('total_modal');
        $summary->aksesoris_penjualan_jual = $transactions->sum('total_penjualan');
        $summary->aksesoris_laba = $transactions->sum('laba');
    }

    private function calculatePengeluaranSummary(DailySummary $summary, Carbon $date, int $userId): void
    {
        $base = Pengeluaran::withoutGlobalScopes()
            ->whereDate('tanggal', $date)
            ->where('user_id', $userId);

        $summary->pengeluaran_operasional = (clone $base)->where('kategori', 'operasional')->sum('jumlah');
        $summary->pengeluaran_gaji = (clone $base)->where('kategori', 'gaji')->sum('jumlah');
        $summary->pengeluaran_pribadi = (clone $base)->where('kategori', 'pribadi')->sum('jumlah');
    }

    public function recalculateRange(Carbon $startDate, Carbon $endDate, int $userId): void
    {
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $this->recalculateForDate($current, $userId);
            $current->addDay();
        }
    }

    public function getDashboardData(Carbon $date, int $userId): array
    {
        $summary = $this->recalculateForDate($date, $userId);

        $dompets = DompetPulsa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->with('adjustments')
            ->get()
            ->map(function ($dompet) use ($date, $userId) {
                $saldoAwal = $this->getSaldoAwalDompet($dompet, $date, $userId);
                $topup = $dompet->topupTransactions()->whereDate('tanggal', $date)->sum('nominal');
                $penjualan = $dompet->penjualanTransactions()->whereDate('tanggal', $date)->sum('nominal');

                $modalAwal = $dompet->saldo_awal;
                $selisih = $modalAwal - $penjualan;
                $sisaSaldoSaatIni = $dompet->sisa_saldo_efektif;
                $labaRugi = $sisaSaldoSaatIni - $selisih;

                return [
                    'id' => $dompet->id,
                    'nama' => $dompet->nama,
                    'kode' => $dompet->kode,
                    'saldo_awal' => $saldoAwal,
                    'topup' => $topup,
                    'penjualan' => $penjualan,
                    'saldo_akhir' => $saldoAwal + $topup - $penjualan,
                    'laba' => $labaRugi,
                ];
            });

        $vouchers = Voucher::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('status', 'aktif')
            ->get()
            ->map(function ($voucher) use ($date) {
                $transactions = $voucher->transactions()->whereDate('tanggal', $date);

                return [
                    'id' => $voucher->id,
                    'nama' => $voucher->nama,
                    'stok' => $voucher->hitungStokTersedia(),
                    'terjual_hari_ini' => $transactions->sum('jumlah'),
                    'laba_hari_ini' => $transactions->sum('laba'),
                ];
            });

        $aksesoris = Aksesoris::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(function ($aksesoris) use ($date) {
                $penjualan = $aksesoris->penjualanTransactions()->whereDate('tanggal', $date);

                return [
                    'id' => $aksesoris->id,
                    'nama' => $aksesoris->nama,
                    'sku' => $aksesoris->sku,
                    'stok' => $aksesoris->hitungStokTersedia(),
                    'terjual_hari_ini' => $penjualan->sum('jumlah'),
                    'laba_hari_ini' => $penjualan->sum('laba'),
                ];
            });

        return [
            'tanggal' => $date->toDateString(),
            'summary' => $summary,
            'dompets' => $dompets,
            'vouchers' => $vouchers,
            'aksesoris' => $aksesoris,
            'total_saldo_pulsa' => $dompets->sum('saldo_akhir'),
            'total_stok_aksesoris' => $aksesoris->sum('stok'),
        ];
    }
}
