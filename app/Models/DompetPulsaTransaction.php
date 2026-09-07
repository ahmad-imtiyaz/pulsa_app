<?php

namespace App\Models;

use Database\Factories\DompetPulsaTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DompetPulsaTransaction extends Model
{
    /** @use HasFactory<DompetPulsaTransactionFactory> */
    use HasFactory;

    protected $table = 'dompet_pulsa_transactions';

    protected $fillable = [
        'dompet_pulsa_id',
        'jenis',
        'tanggal',
        'nominal',
        'harga_jual',
        'laba',
        'nomor_hp',
        'provider',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'laba' => 'decimal:2',
    ];

    public function dompetPulsa(): BelongsTo
    {
        return $this->belongsTo(DompetPulsa::class, 'dompet_pulsa_id');
    }

    public function scopeTopup($query)
    {
        return $query->where('jenis', 'topup');
    }

    public function scopePenjualan($query)
    {
        return $query->where('jenis', 'penjualan');
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }
}
