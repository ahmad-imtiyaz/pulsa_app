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
    public function recalculateForDate(Carbon $date): DailySummary
    {
        return DB::transaction(function () use ($date) {
            $summary = DailySummary::firstOrCreate(['tanggal' => $date->toDateString()]);

            // 1. Calculate Pulsa Summary
            $this->calculatePulsaSummary($summary, $date);

            // 2. Calculate Voucher Summary
            $this->calculateVoucherSummary($summary, $date);

            // 3. Calculate Aksesoris Summary
            $this->calculateAksesorisSummary($summary, $date);

            // 4. Calculate Total Laba Kotor
            $summary->total_laba_kotor =
                $summary->pulsa_laba +
                $summary->voucher_laba +
                $summary->aksesoris_laba;

            // 5. Calculate Pengeluaran
            $this->calculatePengeluaranSummary($summary, $date);

            // 6. Calculate Sisa Laba
            $summary->total_pengeluaran =
                $summary->pengeluaran_operasional +
                $summary->pengeluaran_gaji +
                $summary->pengeluaran_pribadi;

            $summary->sisa_laba = $summary->total_laba_kotor - $summary->total_pengeluaran;

            $summary->save();

            return $summary->fresh();
        });
    }

    private function calculatePulsaSummary(DailySummary $summary, Carbon $date): void
    {
        $dompets = DompetPulsa::where('is_active', true)->get();

        $totalSaldoAwal = 0;
        $totalTopup = 0;
        $totalPenjualan = 0;
        $totalLabaRugi = 0;

        foreach ($dompets as $dompet) {
            // Saldo awal hari ini = saldo akhir kemarin
            $saldoAwal = $this->getSaldoAwalDompet($dompet, $date);
            $totalSaldoAwal += $saldoAwal;

            // Topup hari ini
            $topup = $dompet->topupTransactions()
                ->whereDate('tanggal', $date)
                ->sum('nominal');
            $totalTopup += $topup;

            // Penjualan hari ini (nominal = penjualan hari ini)
            $penjualanHariIni = $dompet->penjualanTransactions()
                ->whereDate('tanggal', $date)
                ->sum('nominal');
            $totalPenjualan += $penjualanHariIni;

            // Laba/Rugi at wallet level (per dompet)
            // Selisih = Modal Awal - Penjualan Hari Ini
            $modalAwal = $dompet->saldo_awal;
            $selisih = $modalAwal - $penjualanHariIni;
            // Laba/Rugi = Sisa Saldo Saat Ini - Selisih
            $sisaSaldoSaatIni = $dompet->sisa_saldo_awal ?? $dompet->saldo_awal;
            $labaRugi = $sisaSaldoSaatIni - $selisih;
            $totalLabaRugi += $labaRugi;
        }

        $summary->pulsa_saldo_awal = $totalSaldoAwal;
        $summary->pulsa_topup = $totalTopup;
        $summary->pulsa_penjualan = $totalPenjualan;
        $summary->pulsa_saldo_akhir = $totalSaldoAwal + $totalTopup - $totalPenjualan;
        $summary->pulsa_laba = $totalLabaRugi;
    }

    public function getSaldoAwalDompet(DompetPulsa $dompet, Carbon $date): float
    {
        // Cari daily summary kemarin untuk mendapatkan saldo akhir kemarin
        $kemarin = $date->copy()->subDay();
        $summaryKemarin = DailySummary::where('tanggal', $kemarin->toDateString())->first();

        if ($summaryKemarin) {
            // Ambil proporsi saldo kemarin untuk dompet ini
            // Kita hitung berdasarkan transaksi kemarin
            $saldoAkhirKemarin = $dompet->saldo_awal
                + $dompet->topupTransactions()->whereDate('tanggal', '<=', $kemarin)->sum('nominal')
                - $dompet->penjualanTransactions()->whereDate('tanggal', '<=', $kemarin)->sum('nominal');

            return max(0, $saldoAkhirKemarin);
        }

        // Jika tidak ada summary kemarin, gunakan saldo_awal dompet + transaksi sebelum hari ini
        return $dompet->saldo_awal
            + $dompet->topupTransactions()->whereDate('tanggal', '<', $date)->sum('nominal')
            - $dompet->penjualanTransactions()->whereDate('tanggal', '<', $date)->sum('nominal');
    }

    private function calculateVoucherSummary(DailySummary $summary, Carbon $date): void
    {
        $transactions = VoucherTransaction::whereDate('tanggal', $date)->get();

        $summary->voucher_penjualan_modal = $transactions->sum('total_modal');
        $summary->voucher_penjualan_jual = $transactions->sum('total_penjualan');
        $summary->voucher_laba = $transactions->sum('laba');
    }

    private function calculateAksesorisSummary(DailySummary $summary, Carbon $date): void
    {
        $transactions = AksesorisTransaction::where('jenis', 'penjualan')
            ->whereDate('tanggal', $date)
            ->get();

        $summary->aksesoris_penjualan_modal = $transactions->sum('total_modal');
        $summary->aksesoris_penjualan_jual = $transactions->sum('total_penjualan');
        $summary->aksesoris_laba = $transactions->sum('laba');
    }

    private function calculatePengeluaranSummary(DailySummary $summary, Carbon $date): void
    {
        $pengeluaran = Pengeluaran::whereDate('tanggal', $date);

        $summary->pengeluaran_operasional = $pengeluaran->where('kategori', 'operasional')->sum('jumlah');
        $summary->pengeluaran_gaji = $pengeluaran->where('kategori', 'gaji')->sum('jumlah');
        $summary->pengeluaran_pribadi = $pengeluaran->where('kategori', 'pribadi')->sum('jumlah');
    }

    public function recalculateRange(Carbon $startDate, Carbon $endDate): void
    {
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $this->recalculateForDate($current);
            $current->addDay();
        }
    }

    public function getDashboardData(Carbon $date): array
    {
        $summary = DailySummary::where('tanggal', $date->toDateString())->first();

        if (! $summary) {
            $summary = $this->recalculateForDate($date);
        }

        $dompets = DompetPulsa::where('is_active', true)->get()->map(function ($dompet) use ($date) {
            $saldoAwal = $this->getSaldoAwalDompet($dompet, $date);
            $topup = $dompet->topupTransactions()->whereDate('tanggal', $date)->sum('nominal');
            $penjualan = $dompet->penjualanTransactions()->whereDate('tanggal', $date)->sum('nominal');

            // Laba/Rugi at wallet level (per dompet)
            $modalAwal = $dompet->saldo_awal;
            $selisih = $modalAwal - $penjualan;
            $sisaSaldoSaatIni = $dompet->sisa_saldo_awal ?? $dompet->saldo_awal;
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

        $vouchers = Voucher::where('status', 'aktif')->get()->map(function ($voucher) use ($date) {
            $transactions = $voucher->transactions()->whereDate('tanggal', $date);

            return [
                'id' => $voucher->id,
                'nama' => $voucher->nama,
                'stok' => $voucher->hitungStokTersedia(),
                'terjual_hari_ini' => $transactions->sum('jumlah'),
                'laba_hari_ini' => $transactions->sum('laba'),
            ];
        });

        $aksesoris = Aksesoris::where('is_active', true)->get()->map(function ($aksesoris) use ($date) {
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
