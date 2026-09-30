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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    public function recalculateForDate(Carbon $date, int $userId): DailySummary
    {
        $summary = DB::transaction(function () use ($date, $userId) {
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

        $this->clearCacheForDate($date, $userId);

        return $summary;
    }

    public function getCachedSummary(Carbon $date, int $userId): ?DailySummary
    {
        return DailySummary::withoutGlobalScopes()
            ->where('tanggal', $date->toDateString())
            ->where('user_id', $userId)
            ->first();
    }

    public function getOrCalculateSummary(Carbon $date, int $userId): DailySummary
    {
        return $this->recalculateForDate($date, $userId);
    }

    public function getCachedRange(Carbon $startDate, Carbon $endDate, int $userId): Collection
    {
        return DailySummary::withoutGlobalScopes()
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    private function clearCacheForDate(Carbon $date, int $userId): void
    {
        Cache::forget($this->cacheKey($date, $userId));
        Cache::flush(); // Simple approach - in production, use tags or more granular clearing
    }

    private function cacheKey(Carbon $date, int $userId): string
    {
        return "daily_summary_{$userId}_{$date->toDateString()}";
    }

    /**
     * SATU-SATUNYA tempat rumus pulsa per dompet per tanggal.
     * Dipakai oleh laporan (summary) DAN dashboard supaya tidak pernah beda.
     *
     * saldo_awal   = saldo_awal_dompet + topup(<tgl) - penjualan(<tgl) + penyesuaian(<tgl)
     * saldo_sistem = saldo_awal + topup(tgl) - penjualan(tgl)      // saldo yang seharusnya
     * saldo_akhir  = saldo_sistem + penyesuaian(tgl)               // saldo asli
     * laba         = -penjualan_sebelum                          // laba/rugi historis: negatif kumulatif penjualan sebelum tgl ini
     */
    public function hitungDompet(DompetPulsa $dompet, Carbon $date): array
    {
        $tgl = $date->toDateString();

        $adjustments = $dompet->relationLoaded('adjustments')
            ? $dompet->adjustments
            : $dompet->adjustments()->get();

        $net = fn ($list) => (float) $list->sum(
            fn ($a) => $a->jenis === 'tambah' ? $a->nominal : -$a->nominal
        );
        $tglAdj = fn ($a) => Carbon::parse($a->tanggal)->toDateString();

        $adjSebelum = $net($adjustments->filter(fn ($a) => $tglAdj($a) < $tgl));
        $adjHariIni = $net($adjustments->filter(fn ($a) => $tglAdj($a) === $tgl));

        $topupSebelum = (float) $dompet->topupTransactions()->whereDate('tanggal', '<', $tgl)->sum('nominal');
        $modalSebelum = (float) $dompet->penjualanTransactions()->whereDate('tanggal', '<', $tgl)->sum('nominal');

        $topup = (float) $dompet->topupTransactions()->whereDate('tanggal', $tgl)->sum('nominal');
        $penjualanHariIni = $dompet->penjualanTransactions()->whereDate('tanggal', $tgl);
        $hargaModal = (float) (clone $penjualanHariIni)->sum('nominal');
        $hargaJual = (float) (clone $penjualanHariIni)->sum('harga_jual');

        $saldoAwal = (float) $dompet->saldo_awal + $topupSebelum - $modalSebelum + $adjSebelum;
        $saldoSistem = $saldoAwal + $topup - $hargaModal;
        $saldoAkhir = $saldoSistem + $adjHariIni;

        return [
            'modal_awal' => (float) $dompet->saldo_awal,   // dikunci
            'saldo_awal' => $saldoAwal,
            'topup' => $topup,
            'penjualan' => $hargaModal,                    // key lama dipertahankan agar view tidak pecah
            'harga_jual' => $hargaJual,
            'saldo_sistem' => $saldoSistem,
            'penyesuaian' => $adjHariIni,
            'saldo_akhir' => $saldoAkhir,
            'laba' => $hargaJual - $hargaModal,            // laba murni per tanggal
        ];
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
        $totalPenjualanJual = 0;
        $totalSaldoAkhir = 0;
        $totalLaba = 0;

        foreach ($dompets as $dompet) {
            $h = $this->hitungDompet($dompet, $date);

            $totalSaldoAwal += $h['saldo_awal'];
            $totalTopup += $h['topup'];
            $totalPenjualan += $h['penjualan'];
            $totalPenjualanJual += $h['harga_jual'];
            $totalSaldoAkhir += $h['saldo_akhir'];
            $totalLaba += $h['laba'];
        }

        $summary->pulsa_saldo_awal = $totalSaldoAwal;
        $summary->pulsa_topup = $totalTopup;
        $summary->pulsa_penjualan = $totalPenjualan;
        $summary->pulsa_penjualan_jual = $totalPenjualanJual;
        $summary->pulsa_saldo_akhir = $totalSaldoAkhir;
        $summary->pulsa_laba = $totalLaba;
    }

    public function getSaldoAwalDompet(DompetPulsa $dompet, Carbon $date, int $userId): float
    {
        return $this->hitungDompet($dompet, $date)['saldo_awal'];
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
        Cache::flush();
    }

    public function getDashboardData(Carbon $date, int $userId): array
    {
        $summary = $this->getOrCalculateSummary($date, $userId);

        $dompets = DompetPulsa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->with('adjustments')
            ->get()
            ->map(function ($dompet) use ($date) {
                $h = $this->hitungDompet($dompet, $date);

                return [
                    'id' => $dompet->id,
                    'nama' => $dompet->nama,
                    'kode' => $dompet->kode,
                    'saldo_awal' => $h['saldo_awal'],
                    'topup' => $h['topup'],
                    'penjualan' => $h['penjualan'],
                    'saldo_sistem' => $h['saldo_sistem'],
                    'penyesuaian' => $h['penyesuaian'],
                    'saldo_akhir' => $h['saldo_akhir'],
                    'laba' => $h['laba'],
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
