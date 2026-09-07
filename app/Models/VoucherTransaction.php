<?php

namespace App\Models;

use Database\Factories\VoucherTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherTransaction extends Model
{
    /** @use HasFactory<VoucherTransactionFactory> */
    use HasFactory;

    protected $table = 'voucher_transactions';

    protected $fillable = [
        'voucher_id',
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

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }
}
