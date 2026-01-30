@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Kunjungan - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page { margin: 15mm 20mm; }
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
        body { margin: 0; color: #111827; }
        h1, h2, h3 { margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 13px; margin-top: 4px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta-table td { padding: 4px 6px; vertical-align: top; }
        .meta-label { width: 140px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; page-break-inside: auto; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 4px 6px; word-wrap: break-word; }
        table.data th { background: #f3f4f6; font-weight: bold; text-align: center; }
        table.data td { font-size: 10px; }
        .text-center { text-align: center; }
        .small { font-size: 10px; }
        .mt-2 { margin-top: 8px; }
    </style>
</head>
<body>
    <div style="padding: 0 10mm;">
    <div class="header">
        <div class="title">Laporan Absensi Kunjungan (Ibu/Bayi)</div>
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
            <td>: {{ $bulanLabel }}</td>
            <td class="meta-label">Filter Presensi</td>
            <td>: {{ $presensiLabel }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Data</td>
            <td>: {{ $list->count() }} data</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <h3>Daftar {{ $presensiLabel }} (Kunjungan Ibu/Bayi per Bulan)</h3>

    @if ($list->isEmpty())
        <p class="mt-2">Tidak ada data untuk periode dan filter yang dipilih.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Ibu</th>
                    <th>Nama Suami</th>
                    <th>Nama Bayi</th>
                    @if ($presensiLabel === 'Hadir')
                        <th>Tanggal Kunjungan</th>
                    @elseif ($presensiLabel === 'Semua')
                        <th>Status</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($list as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['nama_ibu'] ?? '-' }}</td>
                        <td>{{ $item['nama_suami'] ?? '-' }}</td>
                        <td>{{ $item['nama_bayi'] ?? '-' }}</td>
                        @if ($presensiLabel === 'Hadir')
                            <td class="text-center">{{ $item['tanggal_kunjungan'] ?? '-' }}</td>
                        @elseif ($presensiLabel === 'Semua')
                            <td class="text-center">{{ $item['status'] ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    </div>
</body>
</html>
