<?php

namespace App\Models;

use Database\Factories\PengeluaranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    /** @use HasFactory<PengeluaranFactory> */
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'kategori',
        'tanggal',
        'jumlah',
        'keterangan',
        'karyawan_nama',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function scopeOperasional($query)
    {
        return $query->where('kategori', 'operasional');
    }

    public function scopeGaji($query)
    {
        return $query->where('kategori', 'gaji');
    }

    public function scopePribadi($query)
    {
        return $query->where('kategori', 'pribadi');
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }
}
