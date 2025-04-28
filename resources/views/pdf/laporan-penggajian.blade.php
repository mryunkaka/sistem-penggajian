<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penggajian - {{ $profilWeb['nama_web'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            background-color: #f9f9f9;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-logo {
            max-height: 50px;
        }

        .header-text {
            text-align: right;
            font-size: 12px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tfoot th {
            background-color: #333;
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ is_string($imagePath) && strpos($imagePath, 'http') === 0 ? $imagePath : asset(str_replace(public_path(), '', $imagePath)) }}"
            alt="Logo" class="header-logo">
        <div class="header-text">
            <div class="company-name">{{ $profilWeb['nama_web'] }}</div>
            <div>{{ $profilWeb['alamat_web'] }}</div>
            <div>Telepon: {{ $profilWeb['no_hp'] }}</div>
        </div>
    </div>
    <h2>Laporan Penggajian</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') }}
        hingga {{ \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>Gaji Pokok</th>
                <th>Tunjangan BBM</th>
                <th>Tunjangan Makan</th>
                <th>Tunjangan Jabatan</th>
                <th>Tunjangan Kehadiran</th>
                <th>Tunjangan Lainnya</th>
                <th>Lembur</th>
                <th>Potongan Kehadiran</th>
                <th>Potongan Ijin</th>
                <th>Pot. Terlambat</th>
                <th>Pot. BPJS JHT</th>
                <th>Pot. BPJS KES</th>
                <th>Gaji Kotor</th>
                <th>Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_gaji_pokok = 0;
                $total_tunjangan_bbm = 0;
                $total_tunjangan_makan = 0;
                $total_tunjangan_jabatan = 0;
                $total_tunjangan_kehadiran = 0;
                $total_tunjangan_lainnya = 0;
                $total_lembur = 0;
                $total_potongan_kehadiran = 0;
                $total_potongan_ijin = 0;
                $total_potongan_terlambat = 0;
                $total_pot_bpjs_jht = 0;
                $total_pot_bpjs_kes = 0;
                $total_gaji_kotor = 0;
                $total_gaji_bersih = 0;
            @endphp

            @foreach ($laporanPenggajian as $index => $item)
                @php
                    // Hitung gaji kotor
                    $gaji_kotor =
                        $item['gaji_pokok'] +
                        $item['tunjangan_bbm'] +
                        $item['tunjangan_makan'] +
                        $item['tunjangan_jabatan'] +
                        $item['tunjangan_kehadiran'] +
                        $item['tunjangan_lainnya'] +
                        $item['lembur'];

                    // Menambahkan ke total
                    $total_gaji_pokok += $item['gaji_pokok'];
                    $total_tunjangan_bbm += $item['tunjangan_bbm'];
                    $total_tunjangan_makan += $item['tunjangan_makan'];
                    $total_tunjangan_jabatan += $item['tunjangan_jabatan'];
                    $total_tunjangan_kehadiran += $item['tunjangan_kehadiran'];
                    $total_tunjangan_lainnya += $item['tunjangan_lainnya'];
                    $total_lembur += $item['lembur'];
                    $total_potongan_kehadiran += $item['potongan_kehadiran'];
                    $total_potongan_ijin += $item['potongan_ijin'];
                    $total_potongan_terlambat += $item['potongan_terlambat'];
                    $total_pot_bpjs_jht += $item['pot_bpjs_jht'];
                    $total_pot_bpjs_kes += $item['pot_bpjs_kes'];
                    $total_gaji_kotor += $gaji_kotor;
                    $total_gaji_bersih += $item['gaji_bersih'];
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['user']['name'] }}</td>
                    <td>{{ number_format($item['gaji_pokok'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['tunjangan_bbm'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['tunjangan_makan'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['tunjangan_jabatan'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['tunjangan_kehadiran'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['tunjangan_lainnya'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['lembur'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['potongan_kehadiran'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['potongan_ijin'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['potongan_terlambat'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['pot_bpjs_jht'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td>{{ number_format($gaji_kotor, 0, ',', '.') }}</td>
                    <td>{{ number_format($item['gaji_bersih'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th>{{ number_format($total_gaji_pokok, 0, ',', '.') }}</th>
                <th>{{ number_format($total_tunjangan_bbm, 0, ',', '.') }}</th>
                <th>{{ number_format($total_tunjangan_makan, 0, ',', '.') }}</th>
                <th>{{ number_format($total_tunjangan_jabatan, 0, ',', '.') }}</th>
                <th>{{ number_format($total_tunjangan_kehadiran, 0, ',', '.') }}</th>
                <th>{{ number_format($total_tunjangan_lainnya, 0, ',', '.') }}</th>
                <th>{{ number_format($total_lembur, 0, ',', '.') }}</th>
                <th>{{ number_format($total_potongan_kehadiran, 0, ',', '.') }}</th>
                <th>{{ number_format($total_potongan_ijin, 0, ',', '.') }}</th>
                <th>{{ number_format($total_potongan_terlambat, 0, ',', '.') }}</th>
                <th>{{ number_format($total_pot_bpjs_jht, 0, ',', '.') }}</th>
                <th>{{ number_format($total_pot_bpjs_kes, 0, ',', '.') }}</th>
                <th>{{ number_format($total_gaji_kotor, 0, ',', '.') }}</th>
                <th>{{ number_format($total_gaji_bersih, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
