@php
    use Carbon\Carbon;

    // Deteksi tipe kolom berdasarkan kategori
    $isDetailed = in_array($kategori, ['bayibalita', 'remaja']);
    $isIbuHamil = $kategori === 'ibuhamil';
    $showPendidikan = ! $isIbuHamil && $kategori !== 'bayibalita';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Sasaran {{ $kategoriLabel }} - {{ $posyandu->nama_posyandu }}</title>
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
        .small {
            font-size: 10px;
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
            width: 130px;
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
        table.data .col-alamat {
            width: 120px;
            max-width: 120px;
            word-break: break-all;
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
        .mt-2 { margin-top: 8px; }
    </style>
</head>
<body>
    <div style="padding: 0 10mm;">
    <div class="header">
        <div class="title">Laporan Sasaran {{ $kategoriLabel }}</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
        @if ($posyandu->alamat_posyandu)
            <div class="small">{{ $posyandu->alamat_posyandu }}</div>
        @endif
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Kategori Sasaran</td>
            <td>: {{ $kategoriLabel }}</td>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ $generatedAt->format('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Sasaran</td>
            <td>: {{ $sasaranList->count() }} orang</td>
            <td class="meta-label">Dicetak oleh</td>
            <td>: {{ $user->name ?? '-' }}</td>
        </tr>
    </table>

    <h3>Daftar Sasaran {{ $kategoriLabel }}</h3>

    @if ($sasaranList->isEmpty())
        <p class="mt-2">Belum ada data sasaran untuk kategori ini.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>No KK</th>
                    <th>Tanggal Lahir</th>
                    <th>Jenis Kelamin</th>
                    <th>Umur</th>

                    @if ($showPendidikan)
                        <th>Pendidikan</th>
                    @endif

                    @if ($isDetailed)
                        <th class="col-alamat">Alamat</th>
                        <th>Kepersertaan BPJS</th>
                        <th>Nomor BPJS</th>
                        <th>Nomor Telepon</th>
                    @elseif (! $isIbuHamil)
                        <th class="col-alamat">Alamat</th>
                        <th>Kepersertaan BPJS</th>
                        <th>Nomor BPJS</th>
                        <th>Nomor Telepon</th>
                    @endif

                    @if ($isIbuHamil)
                        <th>Pekerjaan</th>
                        <th class="col-alamat">Alamat</th>
                        <th>RT</th>
                        <th>RW</th>
                        <th>Nama Suami</th>
                        <th>NIK Suami</th>
                        <th>Pekerjaan Suami</th>
                        <th>Kepersertaan BPJS</th>
                        <th>Nomor Telepon</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($sasaranList as $index => $item)
                    @php
                        // Kolom orangtua sengaja tidak ditampilkan di laporan PDF
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->nik_sasaran ?? '-' }}</td>
                        <td>{{ $item->nama_sasaran ?? '-' }}</td>
                        <td>{{ $item->no_kk_sasaran ?? '-' }}</td>
                        <td class="text-center">
                            @if (! empty($item->tanggal_lahir))
                                {{ Carbon::parse($item->tanggal_lahir)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $item->jenis_kelamin ?? '-' }}</td>
                        <td class="text-center">
                            @php
                                $umurLabel = '-';
                                if (! empty($item->tanggal_lahir)) {
                                    $dob = Carbon::parse($item->tanggal_lahir);
                                    $now = Carbon::now();
                                    $tahun = (int) $dob->diffInYears($now);
                                    if ($kategori === 'bayibalita' && $tahun < 1) {
                                        $bulan = (int) $dob->diffInMonths($now);
                                        $umurLabel = $bulan . ' bln';
                                    } else {
                                        $umurLabel = $tahun . ' th';
                                    }
                                } elseif (! is_null($item->umur_sasaran)) {
                                    $umurLabel = (int) $item->umur_sasaran . ' th';
                                }
                            @endphp
                            {{ $umurLabel }}
                        </td>

                        @if ($showPendidikan)
                            <td>{{ $item->pendidikan ?? '-' }}</td>
                        @endif

                        @if ($isDetailed)
                            <td class="col-alamat">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td>
                                @if (! empty($item->kepersertaan_bpjs))
                                    {{ $item->kepersertaan_bpjs }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->nomor_bpjs ?? '-' }}</td>
                            <td>{{ $item->nomor_telepon ?? '-' }}</td>
                        @elseif (! $isIbuHamil)
                            <td class="col-alamat">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td>
                                @if (! empty($item->kepersertaan_bpjs))
                                    {{ $item->kepersertaan_bpjs }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->nomor_bpjs ?? '-' }}</td>
                            <td>{{ $item->nomor_telepon ?? '-' }}</td>
                        @endif

                        @if ($isIbuHamil)
                            <td>{{ $item->pekerjaan ?? '-' }}</td>
                            <td class="col-alamat">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td class="text-center">{{ $item->rt ?? '-' }}</td>
                            <td class="text-center">{{ $item->rw ?? '-' }}</td>
                            <td>{{ $item->nama_suami ?? '-' }}</td>
                            <td>{{ $item->nik_suami ?? '-' }}</td>
                            <td>{{ $item->pekerjaan_suami ?? '-' }}</td>
                            <td>
                                @if (! empty($item->kepersertaan_bpjs))
                                    {{ $item->kepersertaan_bpjs }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->nomor_telepon ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    </div>
</body>
</html>


