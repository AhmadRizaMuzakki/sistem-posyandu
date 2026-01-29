@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi - {{ $posyandu->nama_posyandu }}</title>
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
        <div class="title">Laporan Absensi (Jadwal)</div>
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
            <td>: {{ $jadwalList->count() }} jadwal</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <h3>Daftar Absensi Petugas Kesehatan</h3>

    @if ($jadwalList->isEmpty())
        <p class="mt-2">Tidak ada data absensi untuk periode dan filter yang dipilih.</p>
    @else
        <table class="data mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Petugas Kesehatan</th>
                    <th>Presensi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwalList as $index => $jadwal)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $jadwal->petugasKesehatan->nama_petugas_kesehatan ?? '-' }}</td>
                        <td class="text-center">
                            @if ($jadwal->presensi === 'hadir')
                                Hadir
                            @elseif ($jadwal->presensi === 'tidak_hadir')
                                Tidak Hadir
                            @else
                                Belum Hadir
                            @endif
                        </td>
                        <td>{{ $jadwal->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    </div>
</body>
</html>
