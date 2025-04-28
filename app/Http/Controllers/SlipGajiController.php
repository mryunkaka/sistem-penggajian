<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Unit;
use App\Models\User;
use App\Models\Absensi;
use App\Models\ProfilWeb;
use App\Helpers\Terbilang;
use App\Models\Penggajian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SlipGajiController extends Controller
{
    public function generateSlip($id)
    {
        // Temukan data penggajian berdasarkan ID
        $item = Penggajian::with(['user', 'user.absensi'])->findOrFail($id);

        // Menghitung status absensi
        $absensi = $item->user->absensi()
            ->whereBetween('tanggal', [$item->tanggal_awal_gaji, $item->tanggal_akhir_gaji])
            ->get();

        $hadir = $absensi->where('status', 'hadir')->count();
        $ijin = $absensi->where('status', 'ijin')->count();
        $off = $absensi->where('status', 'off')->count();
        $cuti = $absensi->where('status', 'cuti')->count();
        $alfa = $absensi->where('status', 'alfa')->count();
        $sakit = $absensi->where('status', 'sakit')->count();

        // Hitung jumlah hari antara tanggal awal dan akhir
        $tanggal_awal = Carbon::parse($item->tanggal_awal_gaji);
        $tanggal_akhir = Carbon::parse($item->tanggal_akhir_gaji);
        $jumlah_hari = $tanggal_awal->diffInDays($tanggal_akhir) + 1; // +1 untuk menghitung hari terakhir

        // Generate nomor slip
        $slipNumber = $this->generateSlipNumber($item->id, $tanggal_awal->month, $tanggal_awal->year);

        // Hitung total lembur
        $lembur_data = $absensi->where('lembur', '>', 0);
        $lembur_total = $lembur_data->sum('lembur');

        // Hitung total potongan lain-lain
        $potongan_data = $absensi->where('jumlah_potongan', '>', 0);

        // Mengambil master gaji berdasarkan unit
        $masterGaji = \App\Models\MasterGaji::where('user_id', $item->id)->first();
        $potonganTerlambat = $masterGaji ? $masterGaji->potongan_terlambat : 500;

        // Hitung total keterlambatan
        $totalTerlambat = $absensi->sum('total_menit_terlambat');
        $denda_terlambat = $totalTerlambat * $potonganTerlambat;

        // Mengonversi gaji bersih ke dalam bentuk kata
        $gaji_bersih_text = isset($item->gaji_bersih) ? Terbilang::terbilang($item->gaji_bersih) : 'Data tidak tersedia';

        // Generate tanggal cetak
        $tanggalCetak = $this->printDate();

        // Ambil data profil web
        $unitId = Auth::user()->unit_id;
        $unit = Unit::find($unitId);

        // Default logo jika tidak ada logo_unit atau unit tidak ditemukan
        $defaultLogo = 'https://via.placeholder.com/150';

        $imagePath = ($unit && $unit->logo_unit)
            ? public_path('storage/' . $unit->logo_unit)
            : $defaultLogo;

        // Buat PDF dari view

        $pdf = PDF::loadView('pdf.slip-gaji', compact(
            'denda_terlambat',
            'unit',
            'totalTerlambat',
            'item',
            'gaji_bersih_text',
            'slipNumber',
            'tanggalCetak',
            'imagePath',
            'hadir',
            'ijin',
            'off',
            'cuti',
            'alfa',
            'sakit',
            'lembur_data',
            'lembur_total',
            'potongan_data'
        ));

        // Kembalikan PDF sebagai respons
        $periode = Carbon::parse($tanggal_awal)->translatedFormat('F-Y'); // Ambil bulan-tahun dari tanggal_awal
        $periode = strtoupper($periode); // Jadikan huruf besar semua

        $namaFile = 'PENGGAJIAN-' . strtoupper($item->user->name) . '-' . strtoupper($unit->nama_unit) . '-PERIODE-' . $periode . '.pdf';

        return $pdf->stream($namaFile, ['Attachment' => false]);
    }

    private function generateSlipNumber($id, $month, $year)
    {
        return "SLIP/" . $id . "/" . $month . "/" . $year;
    }

    private function printDate()
    {
        return Carbon::now()->format('d F Y');
    }
}
