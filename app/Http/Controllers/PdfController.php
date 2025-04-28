<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PdfController extends Controller
{
    public function generateLaporanPenggajian()
    {
        $tanggalAwal = session('laporan_tanggal_awal');
        $tanggalAkhir = session('laporan_tanggal_akhir');

        // Validasi tanggal
        if (empty($tanggalAwal) || empty($tanggalAkhir)) {
            return back()->withErrors(['date_error' => 'Tanggal awal dan akhir harus diisi.']);
        }

        try {
            $tanggalAwal = Carbon::parse($tanggalAwal)->format('Y-m-d');
            $tanggalAkhir = Carbon::parse($tanggalAkhir)->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->withErrors(['date_error' => 'Format tanggal tidak valid.']);
        }

        // Ambil semua user berdasarkan unit_id pengguna yang sedang login
        $unitId = Auth::user()->unit_id;
        $users = User::where('unit_id', $unitId)->get();

        // Ambil data unit untuk mendapatkan logo_unit
        $unit = \App\Models\Unit::find($unitId);
        $defaultLogo = 'https://via.placeholder.com/150';

        // Gunakan path relatif untuk logo
        $imagePath = ($unit && $unit->logo_unit)
            ? asset('storage/' . $unit->logo_unit)
            : $defaultLogo;

        $laporanPenggajian = [];

        foreach ($users as $user) {
            // Ambil data penggajian berdasarkan user_id dan rentang tanggal
            $penggajian = \App\Models\Penggajian::where('user_id', $user->id)
                ->whereDate('tanggal_awal_gaji', $tanggalAwal)
                ->whereDate('tanggal_akhir_gaji', $tanggalAkhir)
                ->get();


            if ($penggajian->isEmpty()) {
                continue; // Lewati user jika tidak ada data penggajian
            }

            // Simpan hasil penggajian ke array laporan
            $laporanPenggajian[] = [
                'user' => [
                    'name' => Str::limit(mb_convert_encoding($user->name, 'UTF-8', 'auto'), 50),
                    'email' => mb_convert_encoding($user->email, 'UTF-8', 'auto'),
                ],
                'gaji_pokok' => (float)$penggajian->sum('gaji_pokok') ?: 0,
                'tunjangan_bbm' => (float)$penggajian->sum('tunjangan_bbm') ?: 0,
                'tunjangan_lainnya' => (float)$penggajian->sum('tunjangan_lainnya') ?: 0,
                'tunjangan_makan' => (float)$penggajian->sum('tunjangan_makan') ?: 0,
                'tunjangan_jabatan' => (float)$penggajian->sum('tunjangan_jabatan') ?: 0,
                'tunjangan_kehadiran' => (float)$penggajian->sum('tunjangan_kehadiran') ?: 0,
                'lembur' => (float)$penggajian->sum('lembur') ?: 0,
                'potongan_kehadiran' => (float)$penggajian->sum('potongan_kehadiran') ?: 0,
                'potongan_ijin' => (float)$penggajian->sum('potongan_ijin') ?: 0,
                'potongan_terlambat' => (float)$penggajian->sum('potongan_terlambat') ?: 0,
                'pot_bpjs_jht' => (float)$penggajian->sum('pot_bpjs_jht') ?: 0,
                'pot_bpjs_kes' => (float)$penggajian->sum('pot_bpjs_kes') ?: 0,
                'total_potongan' => (float)$penggajian->sum('total_potongan') ?: 0,
                'gaji_bersih' => (float)$penggajian->sum('gaji_bersih') ?: 0,
            ];
        }

        // Cek apakah ada data penggajian
        if (empty($laporanPenggajian)) {
            return back()->withErrors(['data_error' => 'Tidak ada data penggajian dalam rentang tanggal ini.']);
        }

        try {
            // Siapkan data profil dengan encoding yang benar
            $profilWebData = [
                'nama_web' => $unit?->nama_unit ?? 'Nama Perusahaan Anda',
                'alamat_web' => $unit?->alamat_unit ?? 'Alamat Perusahaan Anda',
                'no_hp' => $unit?->no_hp_unit ?? '08123456789',
            ];

            // Render view PDF sebagai HTML
            $view = view('pdf.laporan-penggajian', [
                'laporanPenggajian' => $laporanPenggajian,
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
                'profilWeb' => $profilWebData,
                'imagePath' => $imagePath,
            ])->render();

            // Buat instance Dompdf
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultEncoding', 'UTF-8');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($view, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            function sanitizeFileName($string)
            {
                return preg_replace('/[^\w\s-]/', '', $string); // Hilangkan karakter aneh
            }

            // Ambil periode dari tanggal awal
            $periode = Carbon::parse($tanggalAwal)->translatedFormat('F-Y');
            $periode = strtoupper($periode);

            // Nama Unit (Hotel/Kantor)
            $namaUnit = sanitizeFileName(strtoupper($unit->nama_unit));

            // Gabungkan nama file
            $namaFile = 'LAPORAN-PENGGAJIAN-' . $namaUnit . '-' . $periode . '.pdf';

            // Output dari Dompdf
            $output = $dompdf->output();

            // Return response manual
            return response($output)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $namaFile . '"');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF generation error: ' . $e->getMessage());
            return back()->withErrors(['pdf_error' => 'Terjadi kesalahan saat membuat PDF: ' . $e->getMessage()]);
        }
    }
}
