<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoucherRequest;
use App\Http\Requests\VoucherTransactionRequest;
use App\Jobs\RecalculateDailySummaries;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
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

    private function queueRecalculateDate(Carbon $date): void
    {
        RecalculateDailySummaries::dispatch($date, $date, Auth::id());
    }

    public function index(Request $request)
    {
        $query = Voucher::with('transactions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $vouchers = $query->latest()->get();

        return view('voucher.index', compact('vouchers'));
    }

    public function create()
    {
        return view('voucher.create');
    }

    public function store(VoucherRequest $request)
    {
        Voucher::create($request->validated());

        return redirect()->route('voucher.index')
            ->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function show(Voucher $voucher)
    {
        $transactions = $voucher->transactions()
            ->latest('tanggal')
            ->paginate(20);

        return view('voucher.show', compact('voucher', 'transactions'));
    }

    public function edit(Voucher $voucher)
    {
        return view('voucher.edit', compact('voucher'));
    }

    public function update(VoucherRequest $request, Voucher $voucher)
    {
        $voucher->update($request->validated());

        $this->queueRecalculateRange();

        return redirect()->route('voucher.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        $this->queueRecalculateRange();

        return redirect()->route('voucher.index')
            ->with('success', 'Voucher berhasil dihapus.');
    }

    public function createTransaction(Voucher $voucher)
    {
        return view('voucher.transactions.create', compact('voucher'));
    }

    public function storeTransaction(VoucherTransactionRequest $request, Voucher $voucher)
    {
        $data = $request->validated();
        $data['voucher_id'] = $voucher->id;
        $data['total_modal'] = $data['harga_modal'] * $data['jumlah'];
        $data['total_penjualan'] = $data['harga_jual'] * $data['jumlah'];
        $data['laba'] = $data['total_penjualan'] - $data['total_modal'];

        VoucherTransaction::create($data);

        $this->queueRecalculateDate(Carbon::parse($data['tanggal']));

        return redirect()->route('voucher.show', $voucher)
            ->with('success', 'Transaksi voucher berhasil ditambahkan.');
    }

    public function editTransaction(Voucher $voucher, VoucherTransaction $transaksi)
    {
        return view('voucher.transactions.edit', compact('voucher', 'transaksi'));
    }

    public function updateTransaction(VoucherTransactionRequest $request, Voucher $voucher, VoucherTransaction $transaksi)
    {
        $data = $request->validated();
        $data['total_modal'] = $data['harga_modal'] * $data['jumlah'];
        $data['total_penjualan'] = $data['harga_jual'] * $data['jumlah'];
        $data['laba'] = $data['total_penjualan'] - $data['total_modal'];

        $transaksi->update($data);

        $this->queueRecalculateDate(Carbon::parse($data['tanggal']));

        return redirect()->route('voucher.show', $voucher)
            ->with('success', 'Transaksi voucher berhasil diperbarui.');
    }

    public function destroyTransaction(Voucher $voucher, VoucherTransaction $transaksi)
    {
        $tanggal = $transaksi->tanggal;
        $transaksi->delete();

        $this->queueRecalculateDate(Carbon::parse($tanggal));

        return redirect()->route('voucher.show', $voucher)
            ->with('success', 'Transaksi voucher berhasil dihapus.');
    }
}
