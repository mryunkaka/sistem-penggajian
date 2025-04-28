<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggajian extends Model
{
    protected $table = 'penggajian';
    protected $fillable = [
        'user_id',
        'tanggal_awal_gaji',
        'tanggal_akhir_gaji',
        'gaji_pokok',
        'gaji_bersih',
        'gaji_kotor',
        'tunjangan_bbm',
        'tunjangan_makan',
        'tunjangan_jabatan',
        'tunjangan_kehadiran',
        'tunjangan_lainnya',
        'lembur',
        'potongan_kehadiran',
        'potongan_ijin',
        'potongan_terlambat',
        'pot_bpjs_jht',
        'pot_bpjs_kes',
        'total_potongan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
