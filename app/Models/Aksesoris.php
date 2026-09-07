<?php

namespace App\Models;

use Database\Factories\AksesorisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aksesoris extends Model
{
    /** @use HasFactory<AksesorisFactory> */
    use HasFactory;

    protected $table = 'aksesoris';

    protected $fillable = [
        'nama',
        'sku',
        'kategori',
        'harga_modal',
        'harga_jual',
        'stok',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'harga_modal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(AksesorisTransaction::class, 'aksesoris_id');
    }

    public function pembelianTransactions(): HasMany
    {
        return $this->transactions()->where('jenis', 'pembelian');
    }

    public function penjualanTransactions(): HasMany
    {
        return $this->transactions()->where('jenis', 'penjualan');
    }

    public function getTotalPembelianAttribute(): int
    {
        return $this->pembelianTransactions()->sum('jumlah');
    }

    public function getTotalPenjualanAttribute(): int
    {
        return $this->penjualanTransactions()->sum('jumlah');
    }

    public function getTotalModalAttribute(): float
    {
        return $this->penjualanTransactions()->sum('total_modal');
    }

    public function getTotalPenjualanJualAttribute(): float
    {
        return $this->penjualanTransactions()->sum('total_penjualan');
    }

    public function getTotalLabaAttribute(): float
    {
        return $this->penjualanTransactions()->sum('laba');
    }

    public function hitungStokTersedia(): int
    {
        return $this->stok + $this->total_pembelian - $this->total_penjualan;
    }

    public function getLabaPerUnitAttribute(): float
    {
        return $this->harga_jual - $this->harga_modal;
    }
}
