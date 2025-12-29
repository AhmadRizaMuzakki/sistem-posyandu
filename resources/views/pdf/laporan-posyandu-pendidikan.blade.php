@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendidikan - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page {
            margin: 15mm 20mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
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
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 13px;
            margin-top: 4px;
        }
        .meta {
            margin-top: 12px;
            font-size: 11px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .meta-table td:first-child {
            width: 30%;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        thead {
            background-color: #f3f4f6;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #d1d5db;
        }
        th {
            font-weight: bold;
            background-color: #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #6b7280;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Data Pendidikan</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
        <div class="meta">
            @if($kategoriLabel && $kategoriLabel !== 'Semua')
                Kategori: {{ $kategoriLabel }}
            @else
                Semua Data Pendidikan
            @endif
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td>Nama Posyandu:</td>
            <td>{{ $posyandu->nama_posyandu }}</td>
        </tr>
        <tr>
            <td>Alamat Posyandu:</td>
            <td>{{ $posyandu->alamat_posyandu ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cetak:</td>
            <td>{{ $generatedAt->format('d F Y, H:i:s') }} WIB</td>
        </tr>
        <tr>
            <td>Dicetak Oleh:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td>Total Data:</td>
            <td>{{ $pendidikanList->count() }} data</td>
        </tr>
    </table>

    @if($pendidikanList->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 15%;">NIK</th>
                    <th style="width: 20%;">Nama</th>
                    <th style="width: 12%;" class="text-center">Tanggal Lahir</th>
                    <th style="width: 10%;" class="text-center">Jenis Kelamin</th>
                    <th style="width: 8%;" class="text-center">Umur</th>
                    <th style="width: 20%;">Pendidikan Terakhir</th>
                    <th style="width: 10%;">Kategori Sasaran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendidikanList as $index => $pendidikan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $pendidikan->nik ?? '-' }}</td>
                        <td>{{ $pendidikan->nama }}</td>
                        <td class="text-center">
                            @if($pendidikan->tanggal_lahir)
                                {{ Carbon::parse($pendidikan->tanggal_lahir)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $pendidikan->jenis_kelamin ?? '-' }}</td>
                        <td class="text-center">
                            @if($pendidikan->umur)
                                {{ $pendidikan->umur }} tahun
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $pendidikan->pendidikan_terakhir ?? '-' }}</td>
                        <td>{{ ucfirst($pendidikan->kategori_sasaran ?? '-') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 20px; color: #6b7280;">
            <p>Tidak ada data pendidikan untuk ditampilkan.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dicetak pada {{ $generatedAt->format('d F Y, H:i:s') }} WIB</p>
    </div>
</body>
</html>

