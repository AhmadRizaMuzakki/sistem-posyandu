@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Imunisasi - {{ $posyandu->nama_posyandu }}</title>
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
        .meta-label {
            width: 120px;
            font-weight: bold;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: auto;
        }
        table.data thead {
            display: table-header-group;
        }
        table.data tfoot {
            display: table-row-group;
        }
        table.data tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            word-wrap: break-word;
            white-space: normal;
        }
        table.data th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
        table.data td {
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .small {
            font-size: 10px;
        }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
    </style>
</head>
<body>
    <div style="padding: 0 10mm;">
    <div class="header">
        <div class="title">Laporan Imunisasi Posyandu</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
        @if ($posyandu->alamat_posyandu)
            <div class="small">{{ $posyandu->alamat_posyandu }}</div>
        @endif
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Dicetak oleh</td>
            <td>: {{ $user->name ?? '-' }}</td>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ Carbon::now('Asia/Jakarta')->format('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Data Imunisasi</td>
            <td>: {{ $imunisasiList->count() }} data</td>
            <td class="meta-label">Periode Data</td>
            <td>
                @php
                    $first = $imunisasiList->min('tanggal_imunisasi');
                    $last = $imunisasiList->max('tanggal_imunisasi');
                @endphp
                :
                @if ($first && $last)
                    {{ Carbon::parse($first)->format('d/m/Y') }}
                    s/d
                    {{ Carbon::parse($last)->format('d/m/Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <h3>Daftar Data Imunisasi</h3>

    @if ($imunisasiList->isEmpty())
        <p class="mt-2">Belum ada data imunisasi untuk Posyandu ini.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Sasaran</th>
                    <th>Kategori Sasaran</th>
                    <th>Tanggal</th>
                    <th>Jenis Imunisasi</th>
                    <th>Tinggi (cm)</th>
                    <th>Berat (kg)</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($imunisasiList as $index => $imunisasi)
                    @php
                        $sasaran = $imunisasi->sasaran;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            @if ($sasaran)
                                {{ $sasaran->nama_sasaran ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ ucfirst($imunisasi->kategori_sasaran) }}</td>
                        <td class="text-center">
                            {{ optional($imunisasi->tanggal_imunisasi)->format('d/m/Y') ?? '-' }}
                        </td>
                        <td>{{ $imunisasi->jenis_imunisasi }}</td>
                        <td class="text-center">
                            {{ $imunisasi->tinggi_badan !== null ? number_format($imunisasi->tinggi_badan, 2) : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $imunisasi->berat_badan !== null ? number_format($imunisasi->berat_badan, 2) : '-' }}
                        </td>
                        <td>
                            {{ $imunisasi->user->name ?? '-' }}
                        </td>
                        <td>{{ $imunisasi->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    </div>
</body>
</html>


