<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $table = 'units';
    protected $fillable = [
        'nama_unit',
        'alamat_unit',
        'no_hp_unit',
        'logo_unit',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function getLogoUnitUrlAttribute(): ?string
    {
        return $this->logo_unit
            ? asset('storage/' . $this->logo_unit)
            : null;
    }
}
