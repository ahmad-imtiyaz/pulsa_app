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

    /** Hitung ulang dari $dari sampai hari ini (saldo terbawa ke hari-hari berikutnya). */
    private function queueRecalculateFrom($dari): void
    {
        $dari = Carbon::parse($dari);
        $sampai = $dari->gt(Carbon::today()) ? $dari->copy() : Carbon::today();

        RecalculateDailySummaries::dispatch($dari, $sampai, Auth::id());
    }

    public function index()
    {
        $dompets = DompetPulsa::with(['transactions' => function ($q) {
            $q->latest('tanggal')->limit(5);
        }, 'adjustments'])->get()
            ->map(function ($dompet) {
                $latestTxDate = $dompet->transactions()->latest('tanggal')->value('tanggal');
                $targetDate = $latestTxDate ? Carbon::parse($latestTxDate) : Carbon::today();
                $h = $this->summaryService->hitungDompet($dompet, $targetDate);

                return [
                    'dompet' => $dompet,
                    'saldo_sekarang' => $h['saldo_akhir'],
                ];
            });

        return view('dompet-pulsa.index', compact('dompets'));
    }

    public function create()
    {
        return view('dompet-pulsa.create');
    }

    public function store(DompetPulsaRequest $request)
    {
        DompetPulsa::create([
            'nama' => $request->nama,
            'kode' => $request->kode,
            'saldo_awal' => $request->saldo_awal,
            'sisa_saldo_awal' => $request->saldo_awal, // kolom lama, tidak dipakai rumus
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

        // Hitung saldo sampai tanggal transaksi terakhir (bukan hari ini)
        // supaya "Saldo Sekarang" konsisten dengan index & laporan stok
        $latestTxDate = $dompetPulsa->transactions()->latest('tanggal')->value('tanggal');
        $targetDate = $latestTxDate ? Carbon::parse($latestTxDate) : Carbon::today();

        $h = $this->summaryService->hitungDompet($dompetPulsa, $targetDate);

        $penjualan = $dompetPulsa->penjualanTransactions();
        $labaTotal = (float) (clone $penjualan)->sum('harga_jual') - (float) (clone $penjualan)->sum('nominal');

        // Untuk card "Hari Ini" tetap pakai hari ini
        $hToday = $this->summaryService->hitungDompet($dompetPulsa, Carbon::today());

        return view('dompet-pulsa.show', [
            'dompetPulsa' => $dompetPulsa,
            'transactions' => $transactions,

            // Saldo terkini (sampai transaksi terakhir) - untuk "Sisa Saldo Saat Ini"
            'saldoAkhir' => $h['saldo_akhir'],
            'modalAwal' => $h['modal_awal'],

            // Angka hari ini (untuk card "Top Up Hari Ini", "Penjualan Hari Ini", dll)
            'saldoAwal' => $hToday['saldo_awal'],
            'topupHariIni' => $hToday['topup'],
            'penjualanHariIni' => $hToday['penjualan'],
            'hargaJualHariIni' => $hToday['harga_jual'],
            'labaHariIni' => $hToday['laba'],

            // Kumulatif semua tanggal
            'labaTotal' => $labaTotal,

            // nama lama, dipertahankan agar view tidak error
            'sisaSaldoSaatIni' => $h['saldo_akhir'],
            'sisaSaldoDisesuaikan' => $h['saldo_akhir'],
            'sisaSaldoEfektif' => $h['saldo_akhir'],
            'selisih' => 0,
            'labaRugi' => $hToday['laba'],
        ]);
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

        $this->queueRecalculateFrom($request->tanggal);

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Penyesuaian saldo berhasil disimpan.');
    }

    public function destroyAdjustment(DompetPulsa $dompetPulsa, DompetPulsaAdjustment $adjustment)
    {
        $tanggal = $adjustment->tanggal;
        $adjustment->delete();

        $this->queueRecalculateFrom($tanggal);

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Penyesuaian saldo berhasil dihapus.');
    }

    public function createTransaction(DompetPulsa $dompetPulsa)
    {
        return view('dompet-pulsa.transactions.create', compact('dompetPulsa'));
    }

    public function storeTransaction(DompetPulsaTransactionRequest $request, DompetPulsa $dompetPulsa)
    {
        $data = $this->normalisasiTransaksi($request->validated());
        $data['dompet_pulsa_id'] = $dompetPulsa->id;

        DompetPulsaTransaction::create($data);

        $this->queueRecalculateFrom($data['tanggal']);

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function editTransaction(DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        return view('dompet-pulsa.transactions.edit', compact('dompetPulsa', 'transaksi'));
    }

    public function updateTransaction(DompetPulsaTransactionRequest $request, DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        $tanggalLama = Carbon::parse($transaksi->tanggal);
        $data = $this->normalisasiTransaksi($request->validated());

        $transaksi->update($data);

        // tanggal bisa berubah: hitung ulang dari yang paling awal
        $this->queueRecalculateFrom($tanggalLama->min(Carbon::parse($data['tanggal'])));

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroyTransaction(DompetPulsa $dompetPulsa, DompetPulsaTransaction $transaksi)
    {
        $tanggal = $transaksi->tanggal;
        $transaksi->delete();

        $this->queueRecalculateFrom($tanggal);

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function updateSaldoOverride(Request $request, DompetPulsa $dompetPulsa)
    {
        $request->validate([
            'saldo_target' => ['required', 'numeric', 'min:0'],
        ]);

        $hari = Carbon::today();
        $dompetPulsa->load('adjustments');

        $h = $this->summaryService->hitungDompet($dompetPulsa, $hari);
        $selisih = round((float) $request->saldo_target - $h['saldo_akhir'], 2);

        if (abs($selisih) < 0.01) {
            return redirect()->route('dompet-pulsa.show', $dompetPulsa)
                ->with('success', 'Saldo sudah sama, tidak ada yang perlu disesuaikan.');
        }

        DompetPulsaAdjustment::create([
            'dompet_pulsa_id' => $dompetPulsa->id,
            'jenis' => $selisih > 0 ? 'tambah' : 'kurang',
            'nominal' => abs($selisih),
            'keterangan' => 'Koreksi saldo sesuai aplikasi',
            'tanggal' => $hari->toDateString(),
        ]);

        $this->queueRecalculateFrom($hari);

        return redirect()->route('dompet-pulsa.show', $dompetPulsa)
            ->with('success', 'Sisa saldo disesuaikan.');
    }

    /**
     * Laporan Stok / Ringkasan Saldo
     * Menggunakan saldo_akhir dari DailySummaryService untuk menghindari double-counting mutasi lampau.
     */
    public function stok()
    {
        $hari = Carbon::today();

        $dompets = DompetPulsa::where('is_active', true)
            ->with('adjustments')
            ->get()
            ->map(function ($dompet) use ($hari) {
                $h = $this->summaryService->hitungDompet($dompet, $hari);

                $topup = (float) $dompet->topupTransactions()
                    ->whereDate('tanggal', '<=', $hari)
                    ->sum('nominal');

                $penjualan = (float) $dompet->penjualanTransactions()
                    ->whereDate('tanggal', '<=', $hari)
                    ->sum('nominal');

                return [
                    'nama' => $dompet->nama,
                    'kode' => $dompet->kode,
                    'saldo_awal' => (float) $dompet->saldo_awal,
                    'total_topup' => $topup,
                    'total_penjualan' => $penjualan, // total harga modal terpakai
                    'penyesuaian' => $h['saldo_akhir'] - ((float) $dompet->saldo_awal + $topup - $penjualan),
                    'saldo_sekarang' => $h['saldo_akhir'], // konsisten dengan dashboard
                ];
            });

        return view('reports.stok', compact('dompets'));
    }

    /**
     * nominal = harga modal (saldo yang terpotong untuk penjualan) atau jumlah topup.
     * Laba selalu dihitung di server, apa pun yang dikirim form.
     */
    private function normalisasiTransaksi(array $data): array
    {
        if ($data['jenis'] === 'penjualan') {
            $data['harga_jual'] = (float) $data['harga_jual'];
            $data['laba'] = $data['harga_jual'] - (float) $data['nominal'];
        } else {
            $data['harga_jual'] = null;
            $data['laba'] = null;
        }

        return $data;
    }
}
