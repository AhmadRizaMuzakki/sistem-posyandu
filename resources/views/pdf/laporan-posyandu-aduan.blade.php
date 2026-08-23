@php
    use App\Helpers\AduanOptions;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aduan - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page { margin: 12mm 14mm; }
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
        body { margin: 0; color: #111827; }
        .header { text-align: center; margin-bottom: 14px; }
        .title { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 12px; margin-top: 4px; }
        .meta-wrapper { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta-wrapper > tbody > tr > td { width: 50%; vertical-align: top; padding: 0 8px 0 0; }
        .meta-wrapper > tbody > tr > td:last-child { padding: 0 0 0 8px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; vertical-align: top; }
        .meta-table td:first-child { width: 42%; font-weight: bold; }
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .stats-table td { padding: 6px; text-align: center; border: 1px solid #d1d5db; font-weight: bold; }
        .stats-label { font-size: 9px; font-weight: normal; color: #4b5563; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { padding: 6px 5px; border: 1px solid #d1d5db; vertical-align: top; }
        table.data th { background: #e5e7eb; font-weight: bold; text-align: left; }
        table.data tr:nth-child(even) { background: #f9fafb; }
        .text-center { text-align: center; }
        .empty { text-align: center; padding: 24px; color: #6b7280; }
        .footer { margin-top: 16px; text-align: right; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Hasil Pengaduan (Aduan)</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
    </div>

    <table class="meta-wrapper">
        <tr>
            <td>
                <table class="meta-table">
                    <tr>
                        <td>Nama Posyandu</td>
                        <td>{{ $posyandu->nama_posyandu }}</td>
                    </tr>
                    <tr>
                        <td>Alamat Posyandu</td>
                        <td>{{ $posyandu->alamat_posyandu ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Filter Status</td>
                        <td>{{ $statusFilterLabel }}</td>
                    </tr>
                    <tr>
                        <td>Filter Bidang SPM</td>
                        <td>{{ $kategoriFilterLabel }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="meta-table">
                    <tr>
                        <td>Periode</td>
                        <td>{{ $periodeLabel }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Cetak</td>
                        <td>{{ $generatedAt->format('d F Y, H:i:s') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Dicetak Oleh</td>
                        <td>{{ $user->name }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="stats-table">
        <tr>
            <td>{{ $stats['total'] }}<br><span class="stats-label">Total</span></td>
            <td>{{ $stats['menunggu'] }}<br><span class="stats-label">Menunggu</span></td>
            <td>{{ $stats['diproses'] }}<br><span class="stats-label">Diproses</span></td>
            <td>{{ $stats['selesai'] }}<br><span class="stats-label">Selesai</span></td>
            <td>{{ $stats['ditolak'] }}<br><span class="stats-label">Ditolak</span></td>
        </tr>
    </table>

    @if($aduanList->count() > 0)
        <table class="data">
            <thead>
                <tr>
                    <th class="text-center" style="width:4%">No</th>
                    <th style="width:14%">Tanggal</th>
                    <th style="width:12%">Keluarga</th>
                    <th style="width:10%">No. KK</th>
                    <th style="width:14%">Judul</th>
                    <th style="width:10%">Bidang SPM</th>
                    <th style="width:8%">Status</th>
                    <th style="width:18%">Isi Aduan</th>
                    <th style="width:10%">Tanggapan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aduanList as $index => $aduan)
                    @php
                        $orangtua = $orangtuaMap->get((string) $aduan->no_kk);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $aduan->tanggal_aduan?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $orangtua?->nama ?? '-' }}</td>
                        <td>{{ $aduan->no_kk }}</td>
                        <td>{{ $aduan->judul }}</td>
                        <td>{{ AduanOptions::kategoriLabel($aduan->kategori) }}</td>
                        <td>{{ AduanOptions::statusLabel($aduan->status) }}</td>
                        <td>{{ $aduan->isi_aduan }}</td>
                        <td>{{ $aduan->tanggapan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">Tidak ada data aduan untuk filter yang dipilih.</div>
    @endif

    <div class="footer">
        Dokumen ini digenerate otomatis oleh Sistem Posyandu.
    </div>
</body>
</html>
