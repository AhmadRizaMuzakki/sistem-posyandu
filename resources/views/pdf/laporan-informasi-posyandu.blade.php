@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Informasi Posyandu - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page {
            margin: 15mm 16mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            color: #111827;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }
        .logo-wrap {
            text-align: center;
            margin: 8px 0 10px 0;
        }
        .logo-img {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            table-layout: fixed;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9px;
        }
        h3 {
            margin: 0 0 4px 0;
            font-size: 11px;
        }
        .section { margin-bottom: 8px; }
        .info-label {
            width: 140px;
            font-weight: bold;
        }
        .chart-wrap {
            text-align: center;
            margin-top: 2px;
        }
        .chart-img {
            width: 78%;
            max-width: 78%;
            height: auto;
        }
        .chart-note {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Informasi Posyandu</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
    </div>

    @if (!empty($logoPosyanduUri))
        <div class="logo-wrap">
            <img class="logo-img" src="{{ $logoPosyanduUri }}" alt="Logo {{ $posyandu->nama_posyandu }}">
        </div>
    @endif

    <div class="section">
        <h3>Informasi Posyandu</h3>
        <table class="meta-table">
            <tr>
                <td class="info-label">Nama Posyandu</td>
                <td>: {{ $posyandu->nama_posyandu ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Alamat</td>
                <td>: {{ $posyandu->alamat_posyandu ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Domisili</td>
                <td>: {{ $posyandu->domisili_posyandu ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Jumlah Sasaran</td>
                <td>: {{ number_format($totalSasaran, 0, ',', '.') }} orang</td>
            </tr>
            <tr>
                <td class="info-label">Total Kader</td>
                <td>: {{ number_format($totalKader, 0, ',', '.') }} orang</td>
            </tr>
            <tr>
                <td class="info-label">Dicetak oleh</td>
                <td>: {{ $user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Petugas</td>
                <td>: {{ $petugasPosyanduLabel ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Tanggal Cetak</td>
                <td>: {{ $generatedAt->format('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Statistik Sasaran per Kategori</h3>
        <div class="chart-wrap">
            @if (!empty($statistikChartUri))
                <img class="chart-img" src="{{ $statistikChartUri }}" alt="Chart Statistik Sasaran">
                <div class="chart-note">Total sasaran: {{ number_format($totalSasaran, 0, ',', '.') }} orang</div>
            @else
                <p>Belum ada data statistik sasaran.</p>
            @endif
        </div>
    </div>

    <div class="section">
        <h3>Ringkasan Pendidikan Sasaran</h3>
        <div class="chart-wrap">
            @if (!empty($pendidikanChartUri))
                <img class="chart-img" src="{{ $pendidikanChartUri }}" alt="Chart Pendidikan">
                <div class="chart-note">Total data pendidikan: {{ number_format($totalPendidikan, 0, ',', '.') }} orang</div>
            @else
                <p>Belum ada data pendidikan untuk ditampilkan.</p>
            @endif
        </div>
    </div>
</body>
</html>
