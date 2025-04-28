<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Absensi;
use App\Models\MasterGaji;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class AbsensiImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->count() <= 1) {
            throw new \Exception('File Excel tidak berisi data');
        }

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Lewati header

            $nama       = trim($row[1]);
            $jabatan    = trim($row[2]);
            $tanggal    = $this->parseDate($row[3]);
            $jamMasuk   = trim($row[4]);
            $jamKeluar  = trim($row[5]);
            $shift      = is_numeric($row[6]) ? (int) $row[6] : null;

            if (empty($nama) || empty($tanggal)) continue;

            $statusList = ['SAKIT', 'IZIN', 'ALFA', 'OFF'];

            if (in_array(strtoupper($jamMasuk), $statusList) || in_array(strtoupper($jamKeluar), $statusList)) {
                $status = strtolower($jamMasuk);
                $jamMasukCarbon = null;
                $jamKeluarCarbon = null;
                $totalTerlambat = 0;
            } else {
                $jamMasukCarbon = $this->parseTimeToCarbon($jamMasuk, $tanggal);
                $jamKeluarCarbon = $this->parseTimeToCarbon($jamKeluar, $tanggal);
                $status = $this->determineStatus($jamMasukCarbon, $jamKeluarCarbon);

                $user = User::firstOrCreate(
                    ['name' => $nama],
                    [
                        'email' => Str::slug($nama) . '@example.com',
                        'password' => Hash::make('password'),
                        'jabatan' => $jabatan,
                        'unit_id' => Auth::user()->unit_id,
                        'tanggal_bergabung' => now(),
                    ]
                );

                // Pastikan Master Gaji tersedia
                $masterGaji = MasterGaji::where('user_id', $user->id)->first();
                if (!$masterGaji) {
                    $masterGaji = MasterGaji::create([
                        'user_id' => $user->id,
                        'gaji_pokok' => 1500000,
                        'tunjangan_bbm' => 0,
                        'tunjangan_makan' => 0,
                        'tunjangan_jabatan' => 0,
                        'tunjangan_kehadiran' => 0,
                        'tunjangan_lainnya' => 0,
                        'tunj_bpjs_jht' => 0,
                        'tunj_bpjs_kes' => 0,
                        'potongan_terlambat' => 1000,
                        'pot_bpjs_jht' => 0,
                    ]);
                } elseif ($masterGaji->potongan_terlambat == 0) {
                    $masterGaji->update(['potongan_terlambat' => 1000]);
                }

                $potonganTerlambat = $masterGaji->potongan_terlambat ?? 1000;

                $jabatanLower = strtolower($user->jabatan ?? '');
                $jamMasukSah = match (true) {
                    $jabatanLower !== 'security' && $shift === 1 => '08:00',
                    $jabatanLower !== 'security' && $shift === 2 => '16:00',
                    $jabatanLower !== 'security' && $shift === 3 => '23:00',
                    $jabatanLower === 'security' && $shift === 1 => '07:00',
                    $jabatanLower === 'security' && $shift === 2 => '15:00',
                    $jabatanLower === 'security' && $shift === 3 => '00:00',
                    default => '00:00',
                };

                $jamMasukSahCarbon = $this->parseTimeToCarbon($jamMasukSah, $tanggal);

                $totalTerlambat = 0;
                if ($jamMasukCarbon && $jamMasukSahCarbon && $jamMasukCarbon->greaterThan($jamMasukSahCarbon)) {
                    $totalTerlambat = round(abs($jamMasukCarbon->diffInMinutes($jamMasukSahCarbon)));
                }
            }

            $user = $user ?? User::where('name', $nama)->first();

            // Cek data absensi duplikat
            $existing = Absensi::where('user_id', $user->id)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', $shift)
                ->where('status', $status)
                ->exists();

            if (!$existing) {
                Absensi::create([
                    'user_id' => $user->id,
                    'tanggal' => $tanggal,
                    'shift' => $shift,
                    'status' => $status,
                    'jam_masuk' => $jamMasukCarbon,
                    'jam_keluar' => $jamKeluarCarbon,
                    'total_menit_terlambat' => $totalTerlambat,
                    'jumlah_potongan' => $totalTerlambat * ($potonganTerlambat ?? 1000),
                    'keterangan_potongan' => $totalTerlambat > 0 ? "Terlambat $totalTerlambat menit" : '',
                ]);
            }
        }
    }

    private function parseDate($dateString)
    {
        try {
            if (empty($dateString)) return null;

            if (is_numeric($dateString)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString));
            }

            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::error("Format tanggal salah: {$dateString} | Error: " . $e->getMessage());
            return null;
        }
    }

    private function parseTimeToCarbon($time, $tanggal)
    {
        try {
            if (empty($time)) return null;

            $baseDate = $this->parseDate($tanggal);

            // Jika input Excel numeric (misalnya 0.5 untuk 12:00 siang)
            if (is_numeric($time)) {
                $carbon = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($time));
                return $baseDate->copy()->setTime($carbon->hour, $carbon->minute);
            }

            // Jika format string HH:MM
            if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                [$jam, $menit] = explode(':', $time);
                return $baseDate->copy()->setTime((int)$jam, (int)$menit);
            }

            // Format tidak dikenali
            return null;
        } catch (\Exception $e) {
            Log::error("Format jam salah: {$time} | Error: " . $e->getMessage());
            return null;
        }
    }

    private function determineStatus($jamMasuk, $jamKeluar)
    {
        return ($jamMasuk || $jamKeluar) ? 'hadir' : 'alfa';
    }
}
