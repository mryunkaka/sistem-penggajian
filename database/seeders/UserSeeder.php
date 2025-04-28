<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID unit yang valid
        $unit_hotel_harmony_id = DB::table('units')->where('nama_unit', 'UNIT A')->value('id');
        $unit_guesthouse_ruma_id = DB::table('units')->where('nama_unit', 'UNIT B')->value('id');
        $unit_hotel_gallery_id = DB::table('units')->where('nama_unit', 'UNIT C')->value('id');

        // Insert Owner
        DB::table('users')->insert([
            [
                // 'id' => Str::uuid(),
                'name' => 'John Doe',
                'email' => 'owner@example.com',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Raya Batulicin, Kp. Baru, Kec. Simpang Empat, Kabupaten Tanah Bumbu, Kalimantan Selatan 72271',
                'foto' => null,
                'tanggal_lahir' => '1980-01-01',
                'tempat_lahir' => 'Batulicin',
                'jenis_kelamin' => 'Laki-laki',
                'agama' => 'Islam',
                'status_perkawinan' => 'Menikah',
                'nik' => '1234567890123456',
                'npwp' => '1234567890',
                'jabatan' => 'Owner',
                'unit_id' => $unit_hotel_harmony_id,
                'tanggal_bergabung' => '2020-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert Personalia
        DB::table('users')->insert([
            [
                // 'id' => Str::uuid(),
                'name' => 'Jane Doe',
                'email' => 'personalia@example.com',
                'password' => Hash::make('password123'),
                'role' => 'personalia',
                'no_hp' => '081234567891',
                'alamat' => 'Jl. Raya Batulicin, Kp. Baru, Kec. Simpang Empat, Kabupaten Tanah Bumbu, Kalimantan Selatan 72271',
                'foto' => null,
                'tanggal_lahir' => '1990-05-10',
                'tempat_lahir' => 'Kotabaru',
                'jenis_kelamin' => 'Perempuan',
                'agama' => 'Kristen',
                'status_perkawinan' => 'Belum Menikah',
                'nik' => '1234567890123457',
                'npwp' => '1234567891',
                'jabatan' => 'Personalia',
                'unit_id' => $unit_guesthouse_ruma_id,
                'tanggal_bergabung' => '2021-03-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert Karyawan
        DB::table('users')->insert([
            [
                // 'id' => Str::uuid(),
                'name' => 'Albert Smith',
                'email' => 'karyawan@example.com',
                'password' => Hash::make('password123'),
                'role' => 'karyawan',
                'no_hp' => '081234567892',
                'alamat' => 'Jl. Suryagandamana, Kotabaru Tengah, Kec. Pulau Laut Utara, Kab. Kotabaru, Kalimantan Selatan 72111',
                'foto' => null,
                'tanggal_lahir' => '1995-11-20',
                'tempat_lahir' => 'Banjarmasin',
                'jenis_kelamin' => 'Laki-laki',
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Menikah',
                'nik' => '1234567890123458',
                'npwp' => '1234567892',
                'jabatan' => 'Karyawan',
                'unit_id' => $unit_hotel_gallery_id,
                'tanggal_bergabung' => '2022-06-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
