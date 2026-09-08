<?php

namespace App\Http\Controllers;

use App\Http\Requests\AksesorisRequest;
use App\Http\Requests\AksesorisTransactionRequest;
use App\Models\Aksesoris;
use App\Models\AksesorisTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;

class AksesorisController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index()
    {
        $aksesoris = Aksesoris::with('transactions')->get();

        return view('aksesoris.index', compact('aksesoris'));
    }

    public function create()
    {
        return view('aksesoris.create');
    }

    public function store(AksesorisRequest $request)
    {
        Aksesoris::create($request->validated());

        return redirect()->route('aksesoris.index')
            ->with('success', 'Aksesoris berhasil ditambahkan.');
    }

    public function show(Aksesoris $aksesoris)
    {
        $transactions = $aksesoris->transactions()
            ->latest('tanggal')
            ->paginate(20);

        return view('aksesoris.show', compact('aksesoris', 'transactions'));
    }

    public function edit(Aksesoris $aksesoris)
    {
        return view('aksesoris.edit', compact('aksesoris'));
    }

    public function update(AksesorisRequest $request, Aksesoris $aksesoris)
    {
        $aksesoris->update($request->validated());

        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

        return redirect()->route('aksesoris.index')
            ->with('success', 'Aksesoris berhasil diperbarui.');
    }

    public function destroy(Aksesoris $aksesoris)
    {
        $aksesoris->delete();

        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

        return redirect()->route('aksesoris.index')
            ->with('success', 'Aksesoris berhasil dihapus.');
    }

    public function createTransaction(Aksesoris $aksesoris)
    {
        return view('aksesoris.transactions.create', compact('aksesoris'));
    }

    public function storeTransaction(AksesorisTransactionRequest $request, Aksesoris $aksesoris)
    {
        $data = $request->validated();
        $data['aksesoris_id'] = $aksesoris->id;
        $data['total_modal'] = $data['harga_modal'] * $data['jumlah'];

        if ($data['jenis'] === 'penjualan') {
            $data['total_penjualan'] = $data['harga_jual'] * $data['jumlah'];
            $data['laba'] = $data['total_penjualan'] - $data['total_modal'];
        } else {
            $data['total_penjualan'] = null;
            $data['laba'] = 0;
            $data['harga_jual'] = null;
        }

        $transaction = AksesorisTransaction::create($data);

        $this->summaryService->recalculateForDate(Carbon::parse($data['tanggal']));

        return redirect()->route('aksesoris.show', $aksesoris)
            ->with('success', 'Transaksi aksesoris berhasil ditambahkan.');
    }
}
