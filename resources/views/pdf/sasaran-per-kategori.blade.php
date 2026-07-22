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
            size: A4 landscape;
            margin: 8mm 6mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8px;
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
            margin-bottom: 10px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            margin-top: 3px;
        }
        .small {
            font-size: 8px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table td {
            padding: 3px 5px;
            vertical-align: top;
            font-size: 8px;
        }
        .meta-label {
            width: 110px;
            font-weight: bold;
        }
        h3 {
            font-size: 10px;
            margin-bottom: 4px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            table-layout: fixed;
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
            padding: 3pt 3pt 3pt 3pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            vertical-align: top;
            font-size: 7px;
            line-height: 1.35;
        }
        table.data th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
        }
        table.data .col-no {
            width: 3%;
        }
        table.data .col-nik {
            width: 9%;
        }
        table.data .col-nama {
            width: 10%;
        }
        table.data .col-tgl {
            width: 6%;
        }
        table.data .col-jk {
            width: 6%;
        }
        table.data .col-umur {
            width: 4%;
        }
        table.data .col-pendidikan {
            width: 8%;
        }
        table.data .col-alamat {
            width: 11%;
            word-break: break-word;
        }
        table.data .col-bpjs {
            width: 6%;
        }
        table.data .col-telp {
            width: 7%;
        }

        /* Remaja: 14 kolom — lebar disesuaikan agar Pendidikan tidak terhimpit */
        body.kategori-remaja table.data th,
        body.kategori-remaja table.data td {
            font-size: 6.5px;
        }
        body.kategori-remaja table.data .col-no { width: 2.5%; }
        body.kategori-remaja table.data .col-nik { width: 7.5%; }
        body.kategori-remaja table.data .col-nama { width: 8%; }
        body.kategori-remaja table.data .col-tgl { width: 5%; }
        body.kategori-remaja table.data .col-jk { width: 5.5%; }
        body.kategori-remaja table.data .col-umur { width: 3.5%; }
        body.kategori-remaja table.data .col-pendidikan { width: 7.5%; }
        body.kategori-remaja table.data .col-alamat { width: 9%; }
        body.kategori-remaja table.data .col-bpjs { width: 5.5%; }
        body.kategori-remaja table.data .col-telp { width: 6%; }

        .text-center {
            text-align: center;
        }
        .mt-2 { margin-top: 6px; }
    </style>
</head>
<body class="kategori-{{ $kategori }}">
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
                    <th class="col-no">No</th>
                    <th class="col-nik">NIK</th>
                    <th class="col-nik">No KK</th>
                    <th class="col-nama">Nama</th>
                    <th class="col-tgl">Tgl Lahir</th>
                    <th class="col-jk">Jenis Kelamin</th>
                    <th class="col-umur">Umur</th>

                    @if ($showPendidikan)
                        <th class="col-pendidikan">Pendidikan</th>
                    @endif

                    @if ($isDetailed)
                        <th class="col-alamat">Alamat</th>
                        <th class="col-bpjs">Kepesertaan BPJS</th>
                        <th class="col-nik">Nomor BPJS</th>
                        <th class="col-telp">No. Telepon</th>
                        <th class="col-nik">NIK Orang Tua</th>
                        <th class="col-nama">Nama Orang Tua</th>
                    @elseif (! $isIbuHamil)
                        <th class="col-alamat">Alamat</th>
                        <th class="col-bpjs">Kepesertaan BPJS</th>
                        <th class="col-nik">Nomor BPJS</th>
                        <th class="col-telp">No. Telepon</th>
                    @endif

                    @if ($isIbuHamil)
                        <th>Pekerjaan</th>
                        <th class="col-alamat">Alamat</th>
                        <th class="col-umur">RT</th>
                        <th class="col-umur">RW</th>
                        <th class="col-nama">Nama Suami</th>
                        <th class="col-nik">NIK Suami</th>
                        <th>Pekerjaan Suami</th>
                        <th class="col-bpjs">Kepesertaan BPJS</th>
                        <th class="col-telp">No. Telepon</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($sasaranList as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->nik_sasaran ?? '-' }}</td>
                        <td>{{ $item->no_kk_sasaran ?? '-' }}</td>
                        <td>{{ $item->nama_sasaran ?? '-' }}</td>
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
                                    if ($kategori === 'bayibalita') {
                                        // Untuk balita, hitung umur dalam bulan berdasarkan tanggal lahir
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
                            <td class="col-pendidikan">{{ $item->pendidikan ?? '-' }}</td>
                        @endif

                        @if ($isDetailed)
                            <td class="col-alamat">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td class="text-center">
                                @if (! empty($item->kepersertaan_bpjs))
                                    {{ $item->kepersertaan_bpjs }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->nomor_bpjs ?? '-' }}</td>
                            <td>{{ $item->nomor_telepon ?? '-' }}</td>
                            <td>{{ $item->orangtua->nik ?? ($item->nik_orangtua ?? '-') }}</td>
                            <td>{{ $item->orangtua->nama ?? '-' }}</td>
                        @elseif (! $isIbuHamil)
                            <td class="col-alamat">{{ $item->alamat_sasaran ?? '-' }}</td>
                            <td class="text-center">
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
                            <td class="text-center">
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
</body>
</html>
