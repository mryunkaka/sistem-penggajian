<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = [
        'user_id',
        'tanggal',
        'status',
        'shift',
        'jam_masuk',
        'jam_keluar',
        'total_menit_terlambat',
        'jumlah_potongan',
        'keterangan_potongan',
        'lembur',
        'keterangan_lembur',
        'potongan_kehadiran',
        'keterangan_kehadiran',
        'potongan_ijin',
        'keterangan_ijin',
        'potongan_khusus',
        'keterangan_khusus',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i',
        'jam_keluar' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
