<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Keluarga - No. KK {{ $noKk }}</title>
    <style>
        @page {
            margin: 15mm 20mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
        }
        body {
            margin: 0;
            color: #111827;
        }
        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 12px;
            margin-top: 4px;
            color: #4b5563;
        }
        .info-section {
            margin-bottom: 12px;
            font-size: 10px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            page-break-inside: auto;
        }
        table.data thead {
            display: table-header-group;
            background-color: #f3f4f6;
        }
        table.data tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        table.data th {
            font-weight: bold;
            background-color: #f3f4f6;
            font-size: 9px;
        }
        table.data td {
            font-size: 9px;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Data Keluarga</div>
        <div class="subtitle">Sistem Informasi Posyandu</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Nama Orangtua:</span>
            <span>{{ $user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">No. Kartu Keluarga:</span>
            <span>{{ $noKk ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span>{{ $generatedAt->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    @if($allKeluarga->count() > 0)
        <table class="data">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 20%;">Nama</th>
                    <th style="width: 15%;">NIK</th>
                    <th style="width: 12%;">Kategori</th>
                    <th style="width: 12%;">Tanggal Lahir</th>
                    <th class="text-center" style="width: 8%;">Umur</th>
                    <th style="width: 12%;">Jenis Kelamin</th>
                    <th style="width: 16%;">Alamat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allKeluarga as $anggota)
                    <tr>
                        <td class="text-center">{{ $anggota['no'] }}</td>
                        <td>{{ $anggota['nama'] }}</td>
                        <td>{{ $anggota['nik'] }}</td>
                        <td>{{ $anggota['kategori'] }}</td>
                        <td>{{ $anggota['tanggal_lahir'] }}</td>
                        <td class="text-center">{{ $anggota['umur'] }}</td>
                        <td>{{ $anggota['jenis_kelamin'] }}</td>
                        <td>{{ $anggota['alamat'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #6b7280;">Tidak ada data keluarga</p>
    @endif

    <div class="footer">
        <div>Total Anggota Keluarga: {{ $allKeluarga->count() }} orang</div>
        <div>Dicetak pada: {{ $generatedAt->format('d/m/Y H:i:s') }}</div>
    </div>
</body>
</html>

