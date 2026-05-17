@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Imunisasi - {{ $posyandu->nama_posyandu }}</title>
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
            width: 140px;
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
        table.data tr {
            page-break-inside: avoid;
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
        .text-center { text-align: center; }
        .small { font-size: 10px; }
        .mt-2 { margin-top: 8px; }
        .badge-hadir { background: #d1fae8; color: #065f46; padding: 2px 6px; border-radius: 4px; }
        .badge-tidak-hadir { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; }
        .signature-wrap { margin-top: 32px; width: 100%; }
        table.signature-ttd { border: none; border-collapse: collapse; width: 100%; font-size: 11px; table-layout: fixed; }
        table.signature-ttd td { border: none; padding: 4px 8px; vertical-align: top; }
        table.signature-ttd .ttd-col-left { width: 50%; text-align: center; }
        table.signature-ttd .ttd-col-right { width: 50%; text-align: center; }
        .ttd-mengetahui { font-style: italic; margin-bottom: 8px; }
        .ttd-jabatan { font-weight: bold; text-transform: uppercase; margin-bottom: 48px; letter-spacing: 0.5px; }
        .ttd-nama { margin-top: 8px; }
        .ttd-nama .ttd-kurung { margin: 0 auto; }
        .ttd-kurung { display: inline-block; border-bottom: 1px solid #111827; width: 160px; max-width: 160px; padding: 0 4px; box-sizing: border-box; }
        .ttd-ketua { font-size: 10px; margin-top: 4px; }
        .galeri-section { margin-top: 28px; page-break-inside: avoid; }
        .galeri-title { font-size: 12px; font-weight: bold; margin-bottom: 10px; text-align: center; }
        table.galeri-grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
        table.galeri-grid td { padding: 6px 6px; vertical-align: top; text-align: center; border: none; }
        .galeri-img-wrap {
            border: 1px solid #d1d5db;
            padding: 6px;
            background: #fafafa;
            width: 100%;
            height: 200px;
            overflow: hidden;
            box-sizing: border-box;
            text-align: center;
        }
        .galeri-img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            max-height: 188px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .galeri-caption { font-size: 9px; margin-top: 4px; color: #374151; line-height: 1.3; }
        .galeri-date { font-size: 9px; font-weight: bold; margin-top: 4px; color: #111827; }
    </style>
</head>
<body>
    <div style="padding: 0 10mm;">
    <div class="header">
        <div class="title">Laporan Kehadiran Imunisasi Posyandu</div>
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
            <td>: {{ $generatedAt->format('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Periode</td>
            <td>: {{ $periodeLabel ?? '' }}</td>
            <td class="meta-label">Kategori Sasaran</td>
            <td>: {{ $kategoriLabel ?? 'Semua' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jenis Vaksin</td>
            <td>: {{ $jenisVaksinLabel ?? 'Semua' }}</td>
            <td class="meta-label">Filter Kehadiran</td>
            <td>: {{ $kehadiranLabel ?? 'Semua' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Sasaran</td>
            <td>: {{ count($rows) }} orang</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <h3>Daftar Sasaran dan Status Kehadiran Imunisasi</h3>

    @if (empty($rows))
        <p class="mt-2">Tidak ada data sasaran untuk filter yang dipilih.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Sasaran</th>
                    <th>Kategori</th>
                    <th>Status Kehadiran</th>
                    <th>Tanggal Imunisasi</th>
                    <th>Jenis Imunisasi</th>
                    <th>Tinggi (cm)</th>
                    <th>Berat (kg)</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
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
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $sasaran->nama_sasaran ?? '-' }}</td>
                        <td class="text-center">{{ $row['kategori_label'] ?? $row['kategori_sasaran'] }}</td>
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
                                {{ number_format($imunisasi->tinggi_badan, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($imunisasi && $imunisasi->berat_badan !== null)
                                {{ number_format($imunisasi->berat_badan, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $imunisasi && $imunisasi->user ? $imunisasi->user->name : '-' }}</td>
                        <td>{{ $imunisasi ? ($imunisasi->keterangan ?? '-') : '-' }}</td>
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
