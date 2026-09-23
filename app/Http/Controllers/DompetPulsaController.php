<?php

namespace App\Http\Controllers;

use App\Http\Requests\DompetPulsaRequest;
use App\Http\Requests\DompetPulsaTransactionRequest;
use App\Jobs\RecalculateDailySummaries;
use App\Models\DompetPulsa;
use App\Models\DompetPulsaAdjustment;
use App\Models\DompetPulsaTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DompetPulsaController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    private function queueRecalculateRange(): void
    {
        RecalculateDailySummaries::dispatch(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30),
            Auth::id()
        );
    }

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

        $this->queueRecalculateRange();

        return redirect()->route('dompet-pulsa.index')
            ->with('success', 'Dompet pulsa berhasil ditambahkan.');
    }

    public function show(DompetPulsa $dompetPulsa)
    {
        $dompetPulsa->load('adjustments');
        $transactions = $dompetPulsa->transactions()
            ->latest('tanggal')
            ->paginate(20);

        $saldoAwal = $this->summaryService->getSaldoAwalDompet($dompetPulsa, Carbon::today(), Auth::id());
        $topupHariIni = $dompetPulsa->topupTransactions()->where('tanggal', Carbon::today())->sum('nominal');
        $penjualanHariIni = $dompetPulsa->penjualanTransactions()->where('tanggal', Carbon::today())->sum('nominal');
        $saldoAkhir = $saldoAwal + $topupHariIni - $penjualanHariIni;

        $modalAwal = $dompetPulsa->modal_awal_efektif;
        $selisih = $modalAwal - $penjualanHariIni;

        $sisaSaldoSaatIni = $dompetPulsa->sisa_saldo_efektif;
        $sisaSaldoDisesuaikan = $dompetPulsa->sisa_saldo_disesuaikan;
        $sisaSaldoEfektif = $dompetPulsa->sisa_saldo_efektif;

        $labaRugi = $sisaSaldoSaatIni - $selisih;

        return view('dompet-pulsa.show', compact(
            'dompetPulsa',
            'transactions',
            'saldoAwal',
            'topupHariIni',
            'penjualanHariIni',
            'saldoAkhir',
            'modalAwal',
            'selisih',
            'sisaSaldoSaatIni',
            'sisaSaldoDisesuaikan',
            'sisaSaldoEfektif',
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

        $this->queueRecalculateRange();

        return redirect()->route('dompet-pulsa.index')
            ->with('success', 'Dompet pulsa berhasil diperbarui.');
    }

    public function destroy(DompetPulsa $dompetPulsa)
    {
        $dompetPulsa->delete();

        $this->queueRecalculateRange();

        return redirect()->route('dompet-pulsa.index')
            ->with('success', 'Dompet pulsa berhasil dihapus.');
    }

    public function storeAdjustment(Request $request, DompetPulsa $dompetPulsa)
    {
        $request->validate([
            'jenis' => ['required', 'in:tambah,kurang'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string'],
            'tanggal' => ['required', 'date'],
        ]);

        DompetPulsaAdjustment::create([
            'dompet_pulsa_id' => $dompetPulsa->id,
            'jenis' => $request->jenis,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);

        RecalculateDailySummaries::dispatch(
            Carbon::parse($request->tanggal),
            Carbon::parse($request->tanggal),
            Auth::id()
        );

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Penyesuaian saldo berhasil disimpan.');
    }

    public function destroyAdjustment(DompetPulsa $dompetPulsa, DompetPulsaAdjustment $adjustment)
    {
        $tanggal = $adjustment->tanggal;
        $adjustment->delete();

        RecalculateDailySummaries::dispatch(
            Carbon::parse($tanggal),
            Carbon::parse($tanggal),
            Auth::id()
        );

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Penyesuaian saldo berhasil dihapus.');
    }

    public function createTransaction(DompetPulsa $dompetPulsa)
    {
        return view('dompet-pulsa.transactions.create', compact('dompetPulsa'));
    }

    public function storeTransaction(DompetPulsaTransactionRequest $request, DompetPulsa $dompetPulsa)
    {
        $data = $request->validated();
        $data['dompet_pulsa_id'] = $dompetPulsa->id;

        $transaction = DompetPulsaTransaction::create($data);

        RecalculateDailySummaries::dispatch(
            Carbon::parse($data['tanggal']),
            Carbon::parse($data['tanggal']),
            Auth::id()
        );

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

        $transaksi->update($data);

        RecalculateDailySummaries::dispatch(
            Carbon::parse($data['tanggal']),
            Carbon::parse($data['tanggal']),
            Auth::id()
        );

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroyTransaction(DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        $tanggal = $transaksi->tanggal;
        $transaksi->delete();

        RecalculateDailySummaries::dispatch(
            Carbon::parse($tanggal),
            Carbon::parse($tanggal),
            Auth::id()
        );

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function updateSaldoOverride(Request $request, DompetPulsa $dompetPulsa)
    {
        $request->validate([
            'saldo_delta' => 'nullable|numeric',
        ]);

        $formulaValue = $dompetPulsa->sisa_saldo_disesuaikan;
        $inputValue = $request->saldo_delta !== '' ? $request->saldo_delta : $formulaValue;
        $delta = $inputValue - $formulaValue;

        $dompetPulsa->update([
            'saldo_delta' => $delta,
        ]);

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Sisa saldo berhasil diperbarui.');
    }
}
