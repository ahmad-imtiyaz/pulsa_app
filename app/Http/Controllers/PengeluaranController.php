<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\DailySummaryService;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index()
    {
        $pengeluaran = Pengeluaran::latest('tanggal')->paginate(20);

        return view('pengeluaran.index', compact('pengeluaran'));
    }

    public function create()
    {
        return view('pengeluaran.create');
    }

    public function store(PengeluaranRequest $request)
    {
        Pengeluaran::create($request->validated());

        $this->summaryService->recalculateForDate(Carbon::parse($request->tanggal));

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function show(Pengeluaran $pengeluaran)
    {
        return view('pengeluaran.show', compact('pengeluaran'));
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        return view('pengeluaran.edit', compact('pengeluaran'));
    }

    public function update(PengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $pengeluaran->update($request->validated());

        $this->summaryService->recalculateForDate(Carbon::parse($request->tanggal));

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $tanggal = $pengeluaran->tanggal;
        $pengeluaran->delete();

        $this->summaryService->recalculateForDate(Carbon::parse($tanggal));

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
