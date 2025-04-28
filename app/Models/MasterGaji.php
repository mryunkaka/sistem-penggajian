<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterGaji extends Model
{
    use HasFactory;

    protected $table = 'master_gaji';

    protected $fillable = [
        'user_id',
        'gaji_pokok',
        'tunjangan_bbm',
        'tunjangan_makan',
        'tunjangan_jabatan',
        'tunjangan_kehadiran',
        'tunjangan_lainnya',
        'potongan_terlambat',
        'pot_bpjs_jht',
        'pot_bpjs_kes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
