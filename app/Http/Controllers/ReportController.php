<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateDailySummaries;
use App\Models\Aksesoris;
use App\Models\AksesorisTransaction;
use App\Models\DompetPulsa;
use App\Models\DompetPulsaTransaction;
use App\Models\Pengeluaran;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Use cached summaries for immediate display
        $summaries = $this->summaryService->getCachedRange(
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            Auth::id()
        );

        // Queue background recalculation for freshness
        RecalculateDailySummaries::dispatch(
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            Auth::id()
        );

        // Get detailed pengeluaran for the date range with pagination
        $pengeluaran = Pengeluaran::whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->groupBy(function ($item) {
                return $item->tanggal->format('Y-m-d');
            });

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
                'total_penjualan' => $transactions->sum('nominal'),
                'transaksi_count' => $transactions->count(),
            ];
        });

        return view('reports.index', compact('summaries', 'totals', 'dompets', 'pengeluaran', 'startDate', 'endDate'));
    }

    public function penjualan(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $userId = Auth::id();

        // Pulsa sales (Isolasi data via relasi dompetPulsa) with pagination
        $pulsaSales = DompetPulsaTransaction::where('jenis', 'penjualan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereHas('dompetPulsa', fn ($q) => $q->withoutGlobalScopes()->where('user_id', $userId))
            ->with('dompetPulsa')
            ->latest('tanggal')
            ->paginate(50);

        // Voucher sales (Isolasi data via relasi voucher) with pagination
        $voucherSales = VoucherTransaction::whereBetween('tanggal', [$startDate, $endDate])
            ->whereHas('voucher', fn ($q) => $q->withoutGlobalScopes()->where('user_id', $userId))
            ->with('voucher')
            ->latest('tanggal')
            ->paginate(50);

        // Aksesoris sales (Isolasi data via relasi aksesoris) with pagination
        $aksesorisSales = AksesorisTransaction::where('jenis', 'penjualan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereHas('aksesoris', fn ($q) => $q->withoutGlobalScopes()->where('user_id', $userId))
            ->with('aksesoris')
            ->latest('tanggal')
            ->paginate(50);

        return view('reports.penjualan', compact('pulsaSales', 'voucherSales', 'aksesorisSales', 'startDate', 'endDate'));
    }

    public function stok(Request $request)
    {
        $dompets = DompetPulsa::where('is_active', true)->get()->map(function ($dompet) {
            $saldoAwal = $this->summaryService->getSaldoAwalDompet($dompet, Carbon::today(), Auth::id());
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
