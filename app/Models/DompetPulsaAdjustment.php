<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DompetPulsaAdjustment extends Model
{
    use HasFactory;

    protected $table = 'dompet_pulsa_adjustments';

    protected $fillable = [
        'dompet_pulsa_id',
        'jenis',
        'nominal',
        'keterangan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function dompetPulsa(): BelongsTo
    {
        return $this->belongsTo(DompetPulsa::class, 'dompet_pulsa_id');
    }
}
