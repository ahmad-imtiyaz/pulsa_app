<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'nama',
        'kode',
        'jenis',
        'nilai',
        'harga_modal',
        'harga_jual',
        'stok',
        'tanggal_berlaku',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'harga_modal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'tanggal_berlaku' => 'date',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(VoucherTransaction::class, 'voucher_id');
    }

    public function getTotalTerjualAttribute(): int
    {
        return $this->transactions()->sum('jumlah');
    }

    public function getTotalModalAttribute(): float
    {
        return $this->transactions()->sum('total_modal');
    }

    public function getTotalPenjualanAttribute(): float
    {
        return $this->transactions()->sum('total_penjualan');
    }

    public function getTotalLabaAttribute(): float
    {
        return $this->transactions()->sum('laba');
    }

    public function hitungStokTersedia(): int
    {
        return $this->stok - $this->total_terjual;
    }

    public function getLabaPerUnitAttribute(): float
    {
        return $this->harga_jual - $this->harga_modal;
    }
}
