<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsensiController extends Controller
{
    public function cetak(Absensi $absensi)
    {
        $absensi = Absensi::with('user')->get();
        $pdf = Pdf::loadView('pdf.absensi', compact('absensi'))
            ->setPaper('A4', 'portrait')
            ->setOption('dpi', 300);
        return $pdf->stream('laporan-absensi.pdf');
    }
}
