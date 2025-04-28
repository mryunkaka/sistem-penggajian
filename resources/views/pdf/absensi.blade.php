<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 30px;
            size: A4;

            @frame header {
                -pdf-frame-content: headerContent;
                top: 10px;
                margin-left: 30px;
                margin-right: 30px;
                height: 80px;
            }

            @frame footer {
                -pdf-frame-content: footerContent;
                bottom: 10px;
                margin-left: 30px;
                margin-right: 30px;
                height: 40px;
            }
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            max-width: 180px;
            margin-bottom: 10px;
        }

        .title {
            color: #2c3e50;
            font-size: 26px;
            font-weight: 600;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .table-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f6 100%);
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #3498db;
            color: white;
            padding: 16px;
            text-align: left;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            font-weight: 600;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e3e6f0;
        }

        tr:hover td {
            background-color: #f8f9fa;
        }

        .status {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }

        .status.Hadir {
            background: #2ecc71;
            color: white;
        }

        .status.Izin {
            background: #f1c40f;
            color: white;
        }

        .status.Alpha {
            background: #e74c3c;
            color: white;
        }

        .footer {
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 40px;
        }

        .shift-tag {
            background: #9b59b6;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="header" id="headerContent">
        @php
            $logoPath = public_path('logo.png');
            $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        @endphp

        @if ($logoData)
            <img src="data:image/png;base64,{{ $logoData }}" alt="Logo Perusahaan">
        @else
            <p><strong>Logo tidak tersedia</strong></p>
        @endif

        alt="Logo Perusahaan">
        <div class="title">Laporan Absensi Karyawan</div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Karyawan</th>
                    <th>Tanggal</th>
                    <th>Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Terlambat</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($absensi as $index => $data)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $data->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y') }}</td>
                        <td>
                            @if ($data->shift)
                                <div class="shift-tag">Shift {{ $data->shift }}</div>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $data->jam_masuk ?? '-' }}</td>
                        <td>{{ $data->jam_keluar ?? '-' }}</td>
                        <td>
                            <div class="status {{ $data->status }}">
                                {{ $data->status ?? '-' }}
                            </div>
                        </td>
                        <td>{{ $data->total_menit_terlambat > 0 ? $data->total_menit_terlambat . ' menit' : '-' }}</td>
                        <td>{{ $data->keterangan_kehadiran ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer" id="footerContent">
        &copy; {{ date('Y') }} Sistem Absensi Digital - Generated at {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
