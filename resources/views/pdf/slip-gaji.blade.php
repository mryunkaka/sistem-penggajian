<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $unit->nama_unit }}</title>
    <style>
        @page {
            size: landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 1px;
            background: white;
            margin: 0;
        }

        .salary-slip {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 50px;
        }

        .salary-slip td {
            padding: 1px;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
        }

        .header-logo {
            max-width: 70px;
            height: auto;
        }

        .company-name {
            text-align: center;
            font-weight: bold;
        }

        .slip-title {
            text-align: center;
            font-weight: bold;
        }

        .header-content {
            text-align: center;
            flex-grow: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 10px; */
            padding: 2px;
            margin-top: 0;
            margin: 0;
            /* Hilangkan margin */
        }

        td,
        th {
            padding: 4px 6px;
            border: 1px solid #000;
        }

        .no-border td {
            border: none;
        }

        .info-table td {
            border: none;
            padding: 2px 6px;
        }

        .total-row {
            font-weight: bold;
        }

        .signature {
            margin-top: 20px;
            text-align: left;
        }

        .notes {
            font-size: 8px;
            font-style: italic;
        }

        /* Tabel wrapper untuk tinggi konsisten */
        .table-wrapper {
            display: table;
            width: 100%;
        }

        .table-wrapper td {
            vertical-align: top;
            height: 100%;
        }

        .inner-table {
            width: 100%;
            border-collapse: collapse;
            height: 100%;
        }

        .inner-table td,
        .inner-table th {
            border: 1px solid #ccc;
            padding: 6px;
        }

        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
    </style>
</head>

<body>
    <table style="border: 1px solid #000;">
        <tr>
            <td>
                <table class="salary-slip" class="no-border">
                    <tr>
                        <td rowspan="3" class="logo-cell">
                            @php
                                $logoData = file_exists($imagePath) ? base64_encode(file_get_contents($imagePath)) : '';
                            @endphp

                            @if ($logoData)
                                <img src="data:image/png;base64,{{ $logoData }}" alt="Logo" class="header-logo">
                            @endif
                        </td>
                        <td width="60%" rowspan="2" class="company-name" style="font-size: 25px">
                            {{ $unit->nama_unit }}
                        </td>
                        <td width="10%">No Slip</td>
                        <td>: {{ $slipNumber }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Cetak</td>
                        <td>: {{ $tanggalCetak }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="slip-title" style="text-align: left">Salary Slip</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="border: 1px solid #000;">
        <tr>
            <td>
                <table class="info-table">
                    <tr>
                        <td width="15%">NIK</td>
                        <td width="35%">: {{ $item->user->nik }}</td>
                        <td width="15%">Periode</td>
                        <td width="35%">: {{ date('d F', strtotime($item->tanggal_awal_gaji)) }} –
                            {{ date('d F Y', strtotime($item->tanggal_akhir_gaji)) }}</td>
                    </tr>
                    <tr>
                        <td>Nama</td>
                        <td>: {{ $item->user->name }}</td>
                        <td>No Rekening</td>
                        <td>: {{ $item->user->no_rekening }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>: {{ $item->user->jabatan }}</td>
                        <td>Bank Tujuan</td>
                        <td>: {{ $item->user->nama_bank }}</td>
                    </tr>
                    <tr>
                        <td>Departemen</td>
                        <td>: {{ $item->user->unit->nama_unit }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="33%">
                <table style="width: 100%; height: 30%">
                    <tr class="section-header">
                        <td colspan="2">KOMPONEN PENDAPATAN</td>
                    </tr>
                    <tr>
                        <td>Gaji Pokok</td>
                        <td align="right">Rp {{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Jabatan</td>
                        <td align="right">
                            @if ($item->tunjangan_jabatan > 0)
                                Rp {{ number_format($item->tunjangan_jabatan, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Tunjangan BBM</td>
                        <td align="right">
                            @if ($item->tunjangan_bbm > 0)
                                Rp {{ number_format($item->tunjangan_bbm, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Tunjangan Lainnya</td>
                        <td align="right">
                            @if ($item->tunjangan_lainnya > 0)
                                Rp {{ number_format($item->tunjangan_lainnya, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Tunjangan Makan</td>
                        <td align="right">
                            @if ($item->tunjangan_makan > 0)
                                Rp {{ number_format($item->tunjangan_makan, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Tunj. Lembur</td>
                        <td align="right">
                            @if ($item->lembur > 0)
                                Rp {{ number_format($item->lembur, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Pendapatan (+)</td>
                        <td align="right">Rp {{ number_format($item->gaji_kotor, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
            <td width="33%">
                <table style="width: 100%; height: 30%">
                    <tr class="section-header">
                        <td colspan="2">POTONGAN PERUSAHAAN & PIHAK KEDUA</td>
                    </tr>
                    <tr>
                        <td>Potongan Kehadiran</td>
                        <td align="right">Rp {{ number_format($item->potongan_kehadiran, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan BPJS JHT</td>
                        <td align="right">Rp {{ number_format($item->pot_bpjs_jht, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan BPJS Kesehatan</td>
                        <td align="right">Rp {{ number_format($item->pot_bpjs_kes, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan Ijin</td>
                        <td align="right">Rp {{ number_format($item->potongan_ijin, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan Lain-Lain</td>
                        <td align="right">Rp {{ number_format($item->potongan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan Terlambat</td>
                        <td align="right">Rp {{ number_format($item->potongan_terlambat, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Potongan Khusus</td>
                        <td align="right">Rp {{ number_format($item->potongan_khusus, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Potongan (-)</td>
                        <td align="right">Rp {{ number_format($item->total_potongan, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
            <td width="33%">
                <table style="width: 100%; height: 30%">
                    <tr class="section-header">
                        <td colspan="2">Detail Tunjangan & Potongan</td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Tunjangan Lembur</td>
                        <td align="left">
                            @if ($lembur_data->count() > 0)
                                <table cellpadding="5" cellspacing="0" width="100%" style="font-size: 7px">
                                    <tbody>
                                        @foreach ($lembur_data as $lembur)
                                            <tr>
                                                <td width="20%">
                                                    {{ \Carbon\Carbon::parse($lembur->tanggal)->format('d-M-Y') }}
                                                </td>
                                                <td width="20%">
                                                    {{ number_format($lembur->uang_lembur, 0, ',', '.') }}</td>
                                                <td>{{ $lembur->keterangan_lembur }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                Tidak ada data lembur.
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Detail Potongan</td>
                        <td align="left">
                            @if ($potongan_data->count() > 0)
                                <table cellpadding="5" cellspacing="0" width="100%" style="font-size: 7px">
                                    <tbody>
                                        @foreach ($potongan_data as $potongan)
                                            <tr>
                                                <td width="20%">
                                                    {{ \Carbon\Carbon::parse($potongan->tanggal)->format('d-M-Y') }}
                                                </td>
                                                <td width="20%">
                                                    {{ number_format($potongan->jumlah_potongan, 0, ',', '.') }}
                                                </td>
                                                <td>{{ $potongan->keterangan_potongan }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                Tidak ada data Potongan Lain.
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Total Keterlambatan</td>
                        <td align="left">
                            @if ($totalTerlambat > 0)
                                <strong>Total :</strong> {{ round($totalTerlambat) }} menit<br>
                                <strong>Total Denda:</strong> Rp{{ number_format($denda_terlambat, 0, ',', '.') }}
                            @else
                                Tidak ada data keterlambatan.
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="33%">
                <table style="width: 100%; height: 20%">
                    <tr class="section-header">
                        <td colspan="2">Detail Absen</td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Hadir</td>
                        <td align="left">{{ $hadir }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%">Ijin</td>
                        <td align="left">{{ $ijin }}</td>
                    </tr>
                    <tr>
                        <td>Off</td>
                        <td align="left">{{ $off }}</td>
                    </tr>
                    <tr>
                        <td>Cuti</td>
                        <td align="left">{{ $cuti }}</td>
                    </tr>
                    <tr>
                        <td>Alfa</td>
                        <td align="left">{{ $alfa }}</td>
                    </tr>
                    <tr>
                        <td>Sakit</td>
                        <td align="left">{{ $sakit }}</td>
                    </tr>
                </table>
            </td>
            <td width="30%" align="right">
                <h3><strong>Total diterima: Rp {{ number_format($item->gaji_bersih, 0, ',', '.') }}</strong><br>
                    <i>Terbilang: {{ $gaji_bersih_text }}</i>
                </h3>
            </td>
        </tr>
    </table>
    <table class="no-border">
        <tr>
            <td width="70%" align="right">Diverifikasi Oleh<br><br><br>Johanes Situmorang</td>
        </tr>
    </table>
</body>

</html>
