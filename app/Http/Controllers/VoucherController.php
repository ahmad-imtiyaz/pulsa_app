<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoucherRequest;
use App\Http\Requests\VoucherTransactionRequest;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index()
    {
        $vouchers = Voucher::with('transactions')->get();

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

        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

        return redirect()->route('voucher.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        $this->summaryService->recalculateRange(
            Carbon::today()->subDays(30),
            Carbon::today()->addDays(30)
        );

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

        $this->summaryService->recalculateForDate(Carbon::parse($data['tanggal']));

        return redirect()->route('voucher.show', $voucher)
            ->with('success', 'Transaksi voucher berhasil ditambahkan.');
    }
}
