@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Imunisasi - {{ $posyandu->nama_posyandu }}</title>
    <style>
        /*
         * Pakai @page margin (bukan nested table + padding).
         * Jangan set margin:0 pada html — di Dompdf style @page menempel ke root <html>,
         * jadi html { margin:0 } menimpa margin halaman (jadi 0).
         * Margin = standar MS Word Normal (2.54 cm / 1 inch).
         */
        @page {
            margin: 2.54cm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            color: #111827;
        }
        .page-content {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .subtitle {
            font-size: 12px;
            margin-top: 3px;
            font-weight: bold;
        }
        .alamat {
            font-size: 10px;
            margin-top: 2px;
            color: #374151;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
            font-size: 10px;
        }
        .meta-label {
            width: 120px;
            font-weight: bold;
        }
        h3 {
            margin: 0 0 6px 0;
            padding: 0;
            font-size: 12px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto;
        }
        table.data thead {
            display: table-header-group;
        }
        table.data tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 4px 5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            vertical-align: middle;
        }
        table.data th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            line-height: 1.25;
        }
        table.data td {
            font-size: 9px;
            line-height: 1.3;
        }
        .col-no { width: 3%; }
        .col-nama { width: 13%; }
        .col-kat { width: 8%; }
        .col-umur { width: 6%; }
        .col-tgl { width: 8%; }
        .col-jenis { width: 12%; }
        .col-tb { width: 7%; }
        .col-bb { width: 7%; }
        .col-td { width: 10%; }
        .col-gd { width: 9%; }
        .col-ket { width: 17%; }
        .text-center { text-align: center; }
        .mt-2 { margin-top: 6px; }
        .mt-1 { margin-top: 4px; }
        .small { font-size: 9px; }
    </style>
</head>
<body>
<div class="page-content">
    <div class="header">
        <div class="title">Laporan Imunisasi Posyandu</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
        @if ($posyandu->alamat_posyandu)
            <div class="alamat">{{ $posyandu->alamat_posyandu }}</div>
        @endif
    </div>

    @php
        if (isset($posyandu) && method_exists($posyandu, 'loadMissing')) {
            $posyandu->loadMissing(['kader.user']);
        }
        $ketuaKader = collect($posyandu->kader ?? [])
            ->first(function ($kader) {
                return strcasecmp((string) ($kader->jabatan_kader ?? ''), 'Ketua') === 0;
            });
        $petugasPosyanduLabel = $ketuaKader
            ? ($ketuaKader->nama_kader ?: ($ketuaKader->user->name ?? '-'))
            : '-';
    @endphp

    <table class="meta-table">
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
                    {{ $filterPeriodeLabel ?? '-' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta-label">Filter Periode</td>
            <td>: {{ $filterPeriodeLabel ?? 'Semua Periode' }}</td>
            <td class="meta-label">Filter Kategori</td>
            <td>: {{ $filterKategoriLabel ?? ($kategoriLabel ?? 'Semua Kategori') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Filter Jenis Vaksin</td>
            <td>: {{ $filterJenisVaksinLabel ?? 'Semua Jenis Vaksin' }}</td>
            <td class="meta-label">Filter Nama</td>
            <td>: {{ $filterNamaSasaranLabel ?? 'Semua Nama Sasaran' }}</td>
        </tr>
        @if (isset($kategoriSasaran) && $kategoriSasaran && !isset($filterKategoriLabel))
        <tr>
            <td class="meta-label">Kategori Sasaran</td>
            <td>: {{ $kategoriLabel ?? ucfirst($kategoriSasaran) }}</td>
            <td></td>
            <td></td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ Carbon::now('Asia/Jakarta')->format('d F Y H:i:s') }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="meta-label">Dicetak oleh</td>
            <td>: {{ $user->name ?? '-' }}</td>
            <td class="meta-label">Ketua</td>
            <td>: {{ $petugasPosyanduLabel }}</td>
        </tr>
    </table>

    <h3>Daftar Data Imunisasi</h3>

    @if ($imunisasiList->isEmpty())
        <p class="mt-2">Belum ada data imunisasi untuk filter yang dipilih pada Posyandu ini.</p>
        <p class="small mt-1">Coba pilih <strong>Semua Tahun</strong>, <strong>Semua Bulan</strong>, dan <strong>Semua Jenis Vaksin</strong>, atau sesuaikan filter dengan tanggal/jenis imunisasi yang ada di daftar.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Nama Sasaran</th>
                    <th class="col-kat">Kategori Sasaran</th>
                    <th class="col-umur">Umur</th>
                    <th class="col-tgl">Tanggal</th>
                    <th class="col-jenis">Jenis Imunisasi</th>
                    <th class="col-tb">Tinggi (cm)</th>
                    <th class="col-bb">Berat (kg)</th>
                    <th class="col-td">Tekanan Darah</th>
                    <th class="col-gd">Gula Darah</th>
                    <th class="col-ket">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($imunisasiList as $index => $imunisasi)
                    @php
                        $sasaran = $imunisasi->sasaran;
                        $umurLabel = '-';
                        if ($sasaran && ! empty($sasaran->tanggal_lahir)) {
                            $dob = Carbon::parse($sasaran->tanggal_lahir);
                            $now = Carbon::now();
                            $totalMonths = (int) $dob->diffInMonths($now);
                            $umurLabel = $totalMonths >= 60
                                ? ((int) $dob->diffInYears($now)).' th'
                                : $totalMonths.' bln';
                        } elseif ($sasaran && ! is_null($sasaran->umur_sasaran ?? null)) {
                            $umur = (int) $sasaran->umur_sasaran;
                            $umurLabel = $umur >= 5 ? $umur.' th' : ($umur * 12).' bln';
                        }
                        $keterangan = trim((string) ($imunisasi->keterangan ?? ''));
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $sasaran->nama_sasaran ?? '-' }}</td>
                        <td class="text-center">{{ ucfirst($imunisasi->kategori_sasaran) }}</td>
                        <td class="text-center">{{ $umurLabel }}</td>
                        <td class="text-center">
                            {{ optional($imunisasi->tanggal_imunisasi)->format('d/m/Y') ?? '-' }}
                        </td>
                        <td>{{ $imunisasi->jenis_imunisasi }}</td>
                        <td class="text-center">
                            {{ $imunisasi->tinggi_badan !== null ? (int) round((float) $imunisasi->tinggi_badan) : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $imunisasi->berat_badan !== null ? number_format((float) $imunisasi->berat_badan, 1, ',', '') : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $imunisasi->tekanan_darah ? $imunisasi->tekanan_darah.' mmHg' : '-' }}
                        </td>
                        <td class="text-center">
                            {{ !is_null($imunisasi->gula_darah) ? number_format($imunisasi->gula_darah, 0, ',', '.').' mg/dL' : '-' }}
                        </td>
                        <td>{{ $keterangan !== '' ? $keterangan : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</body>
</html>
