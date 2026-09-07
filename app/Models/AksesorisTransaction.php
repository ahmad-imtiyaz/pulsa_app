<?php

namespace App\Models;

use Database\Factories\AksesorisTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AksesorisTransaction extends Model
{
    /** @use HasFactory<AksesorisTransactionFactory> */
    use HasFactory;

    protected $table = 'aksesoris_transactions';

    protected $fillable = [
        'aksesoris_id',
        'jenis',
        'tanggal',
        'jumlah',
        'harga_modal',
        'harga_jual',
        'total_modal',
        'total_penjualan',
        'laba',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
        'harga_modal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'total_modal' => 'decimal:2',
        'total_penjualan' => 'decimal:2',
        'laba' => 'decimal:2',
    ];

    public function aksesoris(): BelongsTo
    {
        return $this->belongsTo(Aksesoris::class, 'aksesoris_id');
    }

    public function scopePembelian($query)
    {
        return $query->where('jenis', 'pembelian');
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
