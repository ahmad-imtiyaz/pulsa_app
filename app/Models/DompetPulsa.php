<?php

namespace App\Models;

use Database\Factories\DompetPulsaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DompetPulsa extends Model
{
    /** @use HasFactory<DompetPulsaFactory> */
    use HasFactory;

    protected $table = 'dompet_pulsa';

    protected $fillable = [
        'nama',
        'kode',
        'saldo_awal',
        'sisa_saldo_awal',
        'saldo_tersedia',
        'saldo_delta',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'sisa_saldo_awal' => 'decimal:2',
        'saldo_tersedia' => 'decimal:2',
        'saldo_delta' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(DompetPulsaTransaction::class, 'dompet_pulsa_id');
    }

    public function topupTransactions(): HasMany
    {
        return $this->transactions()->where('jenis', 'topup');
    }

    public function penjualanTransactions(): HasMany
    {
        return $this->transactions()->where('jenis', 'penjualan');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(DompetPulsaAdjustment::class, 'dompet_pulsa_id');
    }

    public function getTotalTopupAttribute(): float
    {
        return $this->topupTransactions()->sum('nominal');
    }

    public function getTotalPenjualanAttribute(): float
    {
        return $this->penjualanTransactions()->sum('nominal');
    }

    public function hitungSaldoTersedia(): float
    {
        return $this->saldo_awal + $this->total_topup - $this->total_penjualan;
    }

    public function getAdjustmentSumAttribute(): float
    {
        $total = 0;
        foreach ($this->adjustments as $adj) {
            $total += $adj->jenis === 'tambah' ? $adj->nominal : -$adj->nominal;
        }

        return $total;
    }

    public function getSisaSaldoDisesuaikanAttribute(): float
    {
        return $this->hitungSaldoTersedia() + $this->adjustment_sum;
    }

    public function getSisaSaldoEfektifAttribute(): float
    {
        return $this->sisa_saldo_disesuaikan + ($this->saldo_delta ?? 0);
    }

    public function getSisaSaldoAwalAttribute(?float $value): float
    {
        return $value ?? $this->saldo_awal;
    }
}
