<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MasterGaji;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_hp',
        'alamat',
        'foto',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'agama',
        'status_perkawinan',
        'nik',
        'npwp',
        'jabatan',
        'role',
        'unit_id',
        'tanggal_bergabung',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class);
    }

    // Di model User
    public function masterGaji()
    {
        return $this->hasOne(MasterGaji::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Event saat user baru dibuat
        static::created(function ($user) {
            // Buat data baru di tabel master_gaji
            MasterGaji::create([
                'user_id' => $user->id, // Menggunakan ID user yang baru dibuat
                'gaji_pokok' => 1500000, // Nilai default gaji pokok
                'tunjangan_bbm' => 0,
                'tunjangan_makan' => 0,
                'tunjangan_jabatan' => 0,
                'tunjangan_kehadiran' => 0,
                'tunjangan_lainnya' => 0,
                'potongan_terlambat' => 0,
                'pot_bpjs_jht' => 0,
                'pot_bpjs_kes' => 0,
            ]);
        });
    }
}
