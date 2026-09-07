<?php

namespace App\Models;

use Database\Factories\DailySummaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    /** @use HasFactory<DailySummaryFactory> */
    use HasFactory;

    protected $table = 'daily_summaries';

    protected $fillable = [
        'tanggal',
        'pulsa_saldo_awal',
        'pulsa_topup',
        'pulsa_penjualan_modal',
        'pulsa_penjualan_jual',
        'pulsa_saldo_akhir',
        'pulsa_laba',
        'voucher_penjualan_modal',
        'voucher_penjualan_jual',
        'voucher_laba',
        'aksesoris_penjualan_modal',
        'aksesoris_penjualan_jual',
        'aksesoris_laba',
        'total_laba_kotor',
        'pengeluaran_operasional',
        'pengeluaran_gaji',
        'pengeluaran_pribadi',
        'total_pengeluaran',
        'sisa_laba',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'pulsa_saldo_awal' => 'decimal:2',
        'pulsa_topup' => 'decimal:2',
        'pulsa_penjualan_modal' => 'decimal:2',
        'pulsa_penjualan_jual' => 'decimal:2',
        'pulsa_saldo_akhir' => 'decimal:2',
        'pulsa_laba' => 'decimal:2',
        'voucher_penjualan_modal' => 'decimal:2',
        'voucher_penjualan_jual' => 'decimal:2',
        'voucher_laba' => 'decimal:2',
        'aksesoris_penjualan_modal' => 'decimal:2',
        'aksesoris_penjualan_jual' => 'decimal:2',
        'aksesoris_laba' => 'decimal:2',
        'total_laba_kotor' => 'decimal:2',
        'pengeluaran_operasional' => 'decimal:2',
        'pengeluaran_gaji' => 'decimal:2',
        'pengeluaran_pribadi' => 'decimal:2',
        'total_pengeluaran' => 'decimal:2',
        'sisa_laba' => 'decimal:2',
    ];

    public function getTotalLabaKotorAttribute(): float
    {
        return $this->pulsa_laba + $this->voucher_laba + $this->aksesoris_laba;
    }

    public function getTotalPengeluaranAttribute(): float
    {
        return $this->pengeluaran_operasional + $this->pengeluaran_gaji + $this->pengeluaran_pribadi;
    }

    public function getSisaLabaAttribute(): float
    {
        return $this->total_laba_kotor - $this->total_pengeluaran;
    }
}
