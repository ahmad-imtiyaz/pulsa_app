<?php

namespace App\Http\Controllers;

use App\Models\Aksesoris;
use App\Models\AksesorisTransaction;
use App\Models\DailySummary;
use App\Models\DompetPulsa;
use App\Models\DompetPulsaTransaction;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $summaries = DailySummary::whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate totals
        $totals = [
            'pulsa_laba' => $summaries->sum('pulsa_laba'),
            'voucher_laba' => $summaries->sum('voucher_laba'),
            'aksesoris_laba' => $summaries->sum('aksesoris_laba'),
            'total_laba_kotor' => $summaries->sum('total_laba_kotor'),
            'pengeluaran_operasional' => $summaries->sum('pengeluaran_operasional'),
            'pengeluaran_gaji' => $summaries->sum('pengeluaran_gaji'),
            'pengeluaran_pribadi' => $summaries->sum('pengeluaran_pribadi'),
            'total_pengeluaran' => $summaries->sum('total_pengeluaran'),
            'sisa_laba' => $summaries->sum('sisa_laba'),
        ];

        // Per dompet pulsa report
        $dompets = DompetPulsa::where('is_active', true)->get()->map(function ($dompet) use ($startDate, $endDate) {
            $transactions = $dompet->penjualanTransactions()
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get();

            return [
                'id' => $dompet->id,
                'nama' => $dompet->nama,
                'kode' => $dompet->kode,
                'total_penjualan_modal' => $transactions->sum('nominal'),
                'total_penjualan_jual' => $transactions->sum('harga_jual'),
                'total_laba' => $transactions->sum('laba'),
                'transaksi_count' => $transactions->count(),
            ];
        });

        return view('reports.index', compact('summaries', 'totals', 'dompets', 'startDate', 'endDate'));
    }

    public function penjualan(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Pulsa sales
        $pulsaSales = DompetPulsaTransaction::where('jenis', 'penjualan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('dompetPulsa')
            ->latest('tanggal')
            ->get();

        // Voucher sales
        $voucherSales = VoucherTransaction::whereBetween('tanggal', [$startDate, $endDate])
            ->with('voucher')
            ->latest('tanggal')
            ->get();

        // Aksesoris sales
        $aksesorisSales = AksesorisTransaction::where('jenis', 'penjualan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('aksesoris')
            ->latest('tanggal')
            ->get();

        return view('reports.penjualan', compact('pulsaSales', 'voucherSales', 'aksesorisSales', 'startDate', 'endDate'));
    }

    public function stok(Request $request)
    {
        $dompets = DompetPulsa::where('is_active', true)->get()->map(function ($dompet) {
            $saldoAwal = $this->summaryService->getSaldoAwalDompet($dompet, Carbon::today());
            $topup = $dompet->topupTransactions()->where('tanggal', '<=', Carbon::today())->sum('nominal');
            $penjualan = $dompet->penjualanTransactions()->where('tanggal', '<=', Carbon::today())->sum('nominal');

            return [
                'nama' => $dompet->nama,
                'kode' => $dompet->kode,
                'saldo_awal' => $dompet->saldo_awal,
                'total_topup' => $topup,
                'total_penjualan' => $penjualan,
                'saldo_sekarang' => $saldoAwal + $topup - $penjualan,
            ];
        });

        $vouchers = Voucher::where('status', 'aktif')->get()->map(function ($voucher) {
            return [
                'nama' => $voucher->nama,
                'kode' => $voucher->kode,
                'stok_awal' => $voucher->stok,
                'terjual' => $voucher->total_terjual,
                'stok_sekarang' => $voucher->hitungStokTersedia(),
                'harga_modal' => $voucher->harga_modal,
                'harga_jual' => $voucher->harga_jual,
            ];
        });

        $aksesoris = Aksesoris::where('is_active', true)->get()->map(function ($aksesoris) {
            return [
                'nama' => $aksesoris->nama,
                'sku' => $aksesoris->sku,
                'kategori' => $aksesoris->kategori,
                'stok_awal' => $aksesoris->stok,
                'pembelian' => $aksesoris->total_pembelian,
                'penjualan' => $aksesoris->total_penjualan,
                'stok_sekarang' => $aksesoris->hitungStokTersedia(),
                'harga_modal' => $aksesoris->harga_modal,
                'harga_jual' => $aksesoris->harga_jual,
            ];
        });

        return view('reports.stok', compact('dompets', 'vouchers', 'aksesoris'));
    }
}
