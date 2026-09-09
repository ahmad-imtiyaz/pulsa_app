<?php

namespace App\Http\Controllers;

use App\Http\Requests\DompetPulsaRequest;
use App\Http\Requests\DompetPulsaTransactionRequest;
use App\Models\DompetPulsa;
use App\Models\DompetPulsaTransaction;
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
            'sisa_saldo_awal' => $request->sisa_saldo_awal ?? $request->saldo_awal,
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

        // Client requested logic
        $modalAwal = $dompetPulsa->saldo_awal;
        $selisih = $modalAwal - $penjualanHariIni;
        // Gunakan sisa_saldo_awal (input manual di awal dompet), jadi tidak berubah dari transaksi harian
        $sisaSaldoSaatIni = $dompetPulsa->sisa_saldo_awal ?? $dompetPulsa->saldo_awal;
        $labaRugi = $sisaSaldoSaatIni - $selisih;

        return view('dompet-pulsa.show', compact(
            'dompetPulsa',
            'transactions',
            'saldoAwal',
            'topupHariIni',
            'penjualanHariIni',
            'labaHariIni',
            'saldoAkhir',
            'modalAwal',
            'selisih',
            'sisaSaldoSaatIni',
            'labaRugi'
        ));
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

        if ($data['jenis'] === 'penjualan') {
            $data['laba'] = $data['harga_jual'] - $data['nominal'];
        } else {
            $data['laba'] = 0;
            $data['harga_jual'] = null;
        }

        $transaction = DompetPulsaTransaction::create($data);

        $this->summaryService->recalculateForDate(Carbon::parse($data['tanggal']));

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function editTransaction(DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        return view('dompet-pulsa.transactions.edit', compact('dompetPulsa', 'transaksi'));
    }

    public function updateTransaction(DompetPulsaTransactionRequest $request, DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        $data = $request->validated();

        if ($data['jenis'] === 'penjualan') {
            $data['laba'] = $data['harga_jual'] - $data['nominal'];
        } else {
            $data['laba'] = 0;
            $data['harga_jual'] = null;
        }

        $transaksi->update($data);

        $this->summaryService->recalculateForDate(Carbon::parse($data['tanggal']));

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroyTransaction(DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        $tanggal = $transaksi->tanggal;
        $transaksi->delete();

        $this->summaryService->recalculateForDate(Carbon::parse($tanggal));

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
