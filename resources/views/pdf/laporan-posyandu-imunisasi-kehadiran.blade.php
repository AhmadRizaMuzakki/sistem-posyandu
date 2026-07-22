@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ !empty($isGlobeReport) ? 'Laporan Kategori Sasaran Posyandu' : 'Laporan Kehadiran Imunisasi' }} - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page {
            margin: 15mm 20mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            color: #111827;
        }
        .page-content {
            width: 94%;
            max-width: 94%;
            margin: 0 auto;
        }
        h1, h2, h3 {
            margin: 0;
            padding: 0;
            font-size: 11px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11px;
            margin-top: 3px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .meta-label {
            width: 110px;
            font-weight: bold;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            page-break-inside: auto;
            table-layout: fixed;
        }
        table.data thead {
            display: table-header-group;
        }
        table.data tr {
            page-break-inside: avoid;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 2px 3px;
            word-wrap: break-word;
            white-space: normal;
            font-size: 8px;
            line-height: 1.25;
        }
        table.data th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .small { font-size: 9px; }
        .mt-2 { margin-top: 6px; }
        .badge-hadir { background: #d1fae8; color: #065f46; padding: 1px 4px; border-radius: 3px; font-size: 7px; }
        .badge-tidak-hadir { background: #fee2e2; color: #991b1b; padding: 1px 4px; border-radius: 3px; font-size: 7px; }
        .signature-wrap { margin-top: 24px; width: 100%; }
        table.signature-ttd { border: none; border-collapse: collapse; width: 100%; font-size: 9px; table-layout: fixed; }
        table.signature-ttd td { border: none; padding: 3px 6px; vertical-align: top; }
        table.signature-ttd .ttd-col-left { width: 50%; text-align: center; }
        table.signature-ttd .ttd-col-right { width: 50%; text-align: center; }
        .ttd-mengetahui { font-style: italic; margin-bottom: 6px; }
        .ttd-jabatan { font-weight: bold; text-transform: uppercase; margin-bottom: 40px; letter-spacing: 0.4px; font-size: 9px; }
        .ttd-nama { margin-top: 6px; }
        .ttd-nama .ttd-kurung { margin: 0 auto; }
        .ttd-kurung { display: inline-block; border-bottom: 1px solid #111827; width: 140px; max-width: 140px; padding: 0 4px; box-sizing: border-box; }
        .ttd-ketua { font-size: 8px; margin-top: 3px; }
        .galeri-section { margin-top: 20px; page-break-inside: avoid; }
        .galeri-title { font-size: 10px; font-weight: bold; margin-bottom: 8px; text-align: center; }
        table.galeri-grid { width: 100%; border-collapse: collapse; margin-bottom: 6px; table-layout: fixed; }
        table.galeri-grid td { padding: 4px 4px; vertical-align: top; text-align: center; border: none; }
        .galeri-img-wrap {
            border: 1px solid #d1d5db;
            padding: 4px;
            background: #fafafa;
            width: 100%;
            height: 160px;
            overflow: hidden;
            box-sizing: border-box;
            text-align: center;
        }
        .galeri-img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            max-height: 148px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .galeri-caption { font-size: 8px; margin-top: 3px; color: #374151; line-height: 1.2; }
        .galeri-date { font-size: 8px; font-weight: bold; margin-top: 3px; color: #111827; }
    </style>
</head>
<body>
    <div class="page-content">
    <div class="header">
        <div class="title">{{ !empty($isGlobeReport) ? 'Laporan Kategori Sasaran Posyandu' : 'Laporan Kehadiran Imunisasi Posyandu' }}</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
        @if ($posyandu->alamat_posyandu)
            <div class="small">{{ $posyandu->alamat_posyandu }}</div>
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
            <td class="meta-label">Periode</td>
            <td>: {{ $periodeLabel ?? '' }}</td>
            <td class="meta-label">Kategori Sasaran</td>
            <td>: {{ $kategoriLabel ?? 'Semua' }}</td>
        </tr>
        @if (empty($isGlobeReport))
        <tr>
            <td class="meta-label">Jenis Vaksin</td>
            <td>: {{ $jenisVaksinLabel ?? 'Semua' }}</td>
            <td class="meta-label">Filter Kehadiran</td>
            <td>: {{ $kehadiranLabel ?? 'Semua' }}</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">Total Sasaran</td>
            <td>: {{ count($rows) }} orang</td>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ $generatedAt->format('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Dicetak oleh</td>
            <td>: {{ $user->name ?? '-' }}</td>
            <td class="meta-label">Petugas</td>
            <td>: {{ $petugasPosyanduLabel }}</td>
        </tr>
    </table>

    <h3>{{ !empty($isGlobeReport) ? 'Daftar Sasaran per Kategori' : 'Daftar Sasaran dan Status Kehadiran Imunisasi' }}</h3>

    @if (empty($rows))
        <p class="mt-2">Tidak ada data sasaran untuk filter yang dipilih.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    @if (!empty($isGlobeReport))
                        <th>Nama Sasaran</th>
                        <th>Tanggal Lahir</th>
                        <th>Kategori</th>
                        <th>Umur</th>
                        <th>Alamat</th>
                    @else
                        <th>No</th>
                        <th>Nama Sasaran</th>
                        <th>Kategori</th>
                        <th>Umur</th>
                        <th>Status Kehadiran</th>
                        <th>Tanggal Imunisasi</th>
                        <th>Jenis Imunisasi</th>
                        <th>Tinggi (cm)</th>
                        <th>Berat (kg)</th>
                        <th>Tekanan Darah</th>
                        <th>Gula Darah</th>
                        <th>Keterangan</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $index => $row)
                    @php
                        $sasaran = $row['sasaran'];
                        $status = $row['status'];
                        $imunisasi = $row['imunisasi'] ?? null;
                    @endphp
                    <tr>
                        @if (!empty($isGlobeReport))
                            <td>{{ $sasaran->nama_sasaran ?? '-' }}</td>
                            <td class="text-center">
                                @if (! empty($sasaran->tanggal_lahir))
                                    {{ Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $row['kategori_label'] ?? $row['kategori_sasaran'] }}</td>
                            <td class="text-center">{{ $row['umur_label'] ?? '-' }}</td>
                            <td>{{ $sasaran->alamat_sasaran ?? '-' }}</td>
                        @else
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $sasaran->nama_sasaran ?? '-' }}</td>
                        <td class="text-center">{{ $row['kategori_label'] ?? $row['kategori_sasaran'] }}</td>
                        <td class="text-center">{{ $row['umur_label'] ?? '-' }}</td>
                        <td class="text-center">
                            @if ($status === 'hadir')
                                <span class="badge-hadir">Hadir</span>
                            @else
                                <span class="badge-tidak-hadir">Tidak Hadir</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($imunisasi && $imunisasi->tanggal_imunisasi)
                                {{ $imunisasi->tanggal_imunisasi->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $imunisasi ? ($imunisasi->jenis_imunisasi ?? '-') : '-' }}</td>
                        <td class="text-center">
                            @if ($imunisasi && $imunisasi->tinggi_badan !== null)
                                {{ (int) round((float) $imunisasi->tinggi_badan) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($imunisasi && $imunisasi->berat_badan !== null)
                                {{ number_format((float) $imunisasi->berat_badan, 1, ',', '') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $imunisasi && $imunisasi->tekanan_darah ? $imunisasi->tekanan_darah.' mmHg' : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $imunisasi && ! is_null($imunisasi->gula_darah) ? number_format($imunisasi->gula_darah, 0, ',', '.').' mg/dL' : '-' }}
                        </td>
                        <td>{{ $imunisasi ? ($imunisasi->keterangan ?? '-') : '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

        {{-- Tanda tangan: rata tengah, lebar sejajar ujung table --}}
        @php
            $nama_posyandu_ttd = trim(preg_replace('/^posyandu\s+/i', '', $posyandu->nama_posyandu ?? ''));
            $jabatan_posyandu = 'POSYANDU ' . strtoupper($nama_posyandu_ttd ?: $posyandu->nama_posyandu);
        @endphp
        <div class="signature-wrap" style="width: 100%;">
            <table class="signature-ttd" style="width: 100%; margin: 0;">
                <tr>
                    <td class="ttd-col-left"><div class="ttd-mengetahui">Mengetahui,</div></td>
                    <td class="ttd-col-right"></td>
                </tr>
                <tr>
                    <td class="ttd-col-left"><div class="ttd-jabatan">Kepala Desa {{ strtoupper($posyandu->domisili_posyandu ?? 'KARANGGAN') }}</div></td>
                    <td class="ttd-col-right"><div class="ttd-jabatan">{{ $jabatan_posyandu }}</div></td>
                </tr>
                <tr>
                    <td class="ttd-col-left"><div class="ttd-nama">( <span class="ttd-kurung">&nbsp;</span> )</div></td>
                    <td class="ttd-col-right"><div class="ttd-nama">( <span class="ttd-kurung">&nbsp;</span> )</div><div class="ttd-ketua">Ketua Kader</div></td>
                </tr>
            </table>
        </div>

        @if (!empty($galeriFotos))
            <div class="galeri-section">
                <div class="galeri-title">Dokumentasi Foto Kegiatan — {{ $periodeLabel ?? '' }}</div>
                @php
                    $galeriMaxKolom = 2;
                    $galeriRows = array_chunk($galeriFotos, 2);
                @endphp
                <table class="galeri-grid">
                    <colgroup>
                        <col style="width: 50%;">
                        <col style="width: 50%;">
                    </colgroup>
                    @foreach ($galeriRows as $fotoRow)
                    <tr>
                        @foreach ($fotoRow as $foto)
                            <td>
                                <div class="galeri-img-wrap">
                                    <img src="{{ $foto['data_uri'] }}" alt="Foto kegiatan" class="galeri-img">
                                </div>
                                <div class="galeri-date">{{ $foto['tanggal_formatted'] }}</div>
                                @if (!empty($foto['caption']))
                                    <div class="galeri-caption">{{ $foto['caption'] }}</div>
                                @endif
                            </td>
                        @endforeach
                        @for ($pad = count($fotoRow); $pad < $galeriMaxKolom; $pad++)
                            <td><div class="galeri-img-wrap"></div></td>
                        @endfor
                    </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>
</body>
</html>
