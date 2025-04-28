<?php

namespace Database\Seeders;

use App\Models\MasterGaji;
use Illuminate\Database\Seeder;

class MasterGajiSeeder extends Seeder
{
    public function run(): void
    {
        MasterGaji::create([
            // 'unit_id' => 1,
            'gaji_pokok' => 1500000,
            'tunjangan_bbm' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_jabatan' => 0,
            'tunjangan_kehadiran' => 0,
            'tunjangan_lainnya' => 0,
            'tunj_bpjs_jht' => 0,
            'tunj_bpjs_kes' => 0,
            // 'lembur' => 0,
            // 'potongan_kehadiran' => 0,
            // 'potongan_ijin' => 0,
            'potongan_terlambat' => 500,
            'pot_bpjs_jht' => 0,
            'pot_bpjs_kes' => 0,
        ]);

        MasterGaji::create([
            'unit_id' => 2,
            'gaji_pokok' => 1500000,
            'tunjangan_bbm' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_jabatan' => 0,
            'tunjangan_kehadiran' => 0,
            'tunjangan_lainnya' => 0,
            'tunj_bpjs_jht' => 0,
            'tunj_bpjs_kes' => 0,
            // 'lembur' => 0,
            // 'potongan_kehadiran' => 0,
            // 'potongan_ijin' => 0,
            'potongan_terlambat' => 500,
            'pot_bpjs_jht' => 0,
            'pot_bpjs_kes' => 0,
        ]);

        MasterGaji::create([
            'unit_id' => 3,
            'gaji_pokok' => 1500000,
            'tunjangan_bbm' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_jabatan' => 0,
            'tunjangan_kehadiran' => 0,
            'tunjangan_lainnya' => 0,
            'tunj_bpjs_jht' => 0,
            'tunj_bpjs_kes' => 0,
            // 'lembur' => 0,
            // 'potongan_kehadiran' => 0,
            // 'potongan_ijin' => 0,
            'potongan_terlambat' => 500,
            'pot_bpjs_jht' => 0,
            'pot_bpjs_kes' => 0,
        ]);

        MasterGaji::create([
            'unit_id' => 4,
            'gaji_pokok' => 1500000,
            'tunjangan_bbm' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_jabatan' => 0,
            'tunjangan_kehadiran' => 0,
            'tunjangan_lainnya' => 0,
            'tunj_bpjs_jht' => 0,
            'tunj_bpjs_kes' => 0,
            // 'lembur' => 0,
            // 'potongan_kehadiran' => 0,
            // 'potongan_ijin' => 0,
            'potongan_terlambat' => 500,
            'pot_bpjs_jht' => 0,
            'pot_bpjs_kes' => 0,
        ]);

        MasterGaji::create([
            'unit_id' => 5,
            'gaji_pokok' => 1500000,
            'tunjangan_bbm' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_jabatan' => 0,
            'tunjangan_kehadiran' => 0,
            'tunjangan_lainnya' => 0,
            'tunj_bpjs_jht' => 0,
            'tunj_bpjs_kes' => 0,
            'potongan_terlambat' => 500,
            'pot_bpjs_jht' => 0,
            'pot_bpjs_kes' => 0,
        ]);
    }
}
