<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index(Request $request)
    {
        $query = Pengeluaran::latest('tanggal');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('karyawan_nama', 'like', "%{$search}%");
            });
        }

        $pengeluaran = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('pengeluaran.partials.table', compact('pengeluaran'))->render(),
                'pagination' => view('pengeluaran.partials.pagination', compact('pengeluaran'))->render(),
            ]);
        }

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
