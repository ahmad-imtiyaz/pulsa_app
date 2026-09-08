<?php

namespace App\Http\Controllers;

use App\Http\Requests\DompetPulsaRequest;
use App\Http\Requests\DompetPulsaTransactionRequest;
use App\Models\DompetPulsa;
use App\Models\DompetPulsaTransaction;
use App\Models\Voucher;
use App\Services\DailySummaryService;
use Carbon\Carbon;

class DompetPulsaController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index()
    {
        $dompets = DompetPulsa::with(['transactions' => function ($q) {
            $q->latest('tanggal')->limit(5);
        }])->get();

        return view('dompet-pulsa.index', compact('dompets'));
    }

    public function create()
    {
        return view('dompet-pulsa.create');
    }

    public function store(DompetPulsaRequest $request)
    {
        $dompet = DompetPulsa::create([
            'nama' => $request->nama,
            'kode' => $request->kode,
            'saldo_awal' => $request->saldo_awal,
            'saldo_tersedia' => $request->saldo_awal,
            'is_active' => $request->boolean('is_active', true),
            'keterangan' => $request->keterangan,
        ]);

        // Recalculate daily summaries from this date onwards
        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

        return redirect()->route('dompet-pulsa.index')
            ->with('success', 'Dompet pulsa berhasil ditambahkan.');
    }

    public function show(DompetPulsa $dompetPulsa)
    {
        $transactions = $dompetPulsa->transactions()
            ->latest('tanggal')
            ->paginate(20);

        $saldoAwal = $this->summaryService->getSaldoAwalDompet($dompetPulsa, Carbon::today());
        $topupHariIni = $dompetPulsa->topupTransactions()->where('tanggal', Carbon::today())->sum('nominal');
        $penjualanHariIni = $dompetPulsa->penjualanTransactions()->where('tanggal', Carbon::today())->sum('nominal');
        $labaHariIni = $dompetPulsa->penjualanTransactions()->where('tanggal', Carbon::today())->sum('laba');
        $saldoAkhir = $saldoAwal + $topupHariIni - $penjualanHariIni;

        return view('dompet-pulsa.show', compact('dompetPulsa', 'transactions', 'saldoAwal', 'topupHariIni', 'penjualanHariIni', 'labaHariIni', 'saldoAkhir'));
    }

    public function edit(DompetPulsa $dompetPulsa)
    {
        return view('dompet-pulsa.edit', compact('dompetPulsa'));
    }

    public function update(DompetPulsaRequest $request, DompetPulsa $dompetPulsa)
    {
        $dompetPulsa->update($request->validated());

        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

        return redirect()->route('dompet-pulsa.index')
            ->with('success', 'Dompet pulsa berhasil diperbarui.');
    }

    public function destroy(DompetPulsa $dompetPulsa)
    {
        $dompetPulsa->delete();

        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

        return redirect()->route('dompet-pulsa.index')
            ->with('success', 'Dompet pulsa berhasil dihapus.');
    }

    // Transaction methods
    public function createTransaction(DompetPulsa $dompetPulsa)
    {
        return view('dompet-pulsa.transactions.create', compact('dompetPulsa'));
    }

    public function storeTransaction(DompetPulsaTransactionRequest $request, DompetPulsa $dompetPulsa)
    {
        $data = $request->validated();
        $data['dompet_pulsa_id'] = $dompetPulsa->id;

        // Apply voucher discount if voucher is selected
        if (! empty($data['voucher_id']) && $data['jenis'] === 'penjualan') {
            $voucher = Voucher::find($data['voucher_id']);
            if ($voucher) {
                // Check voucher stock
                if ($voucher->hitungStokTersedia() <= 0) {
                    return back()->withErrors(['voucher_id' => 'Stok voucher sudah habis.'])->withInput();
                }
                // Reduce harga_jual by voucher nilai
                $data['harga_jual'] = max(0, $data['harga_jual'] - $voucher->nilai);
            }
        }

        if ($data['jenis'] === 'penjualan') {
            $data['laba'] = $data['harga_jual'] - $data['nominal'];
        } else {
            $data['laba'] = 0;
            $data['harga_jual'] = null;
            $data['voucher_id'] = null; // Voucher only for penjualan
        }

        $transaction = DompetPulsaTransaction::create($data);

        $this->summaryService->recalculateForDate(Carbon::parse($data['tanggal']));

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }
}
