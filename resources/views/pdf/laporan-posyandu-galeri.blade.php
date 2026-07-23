<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gambar Kegiatan - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page {
            margin: 15mm 18mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
        }
        body {
            margin: 0;
            color: #111827;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 13px;
            margin-top: 4px;
        }
        .meta {
            margin-top: 8px;
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
        .meta-table td:first-child {
            width: 30%;
            font-weight: bold;
        }
        .empty {
            text-align: center;
            padding: 24px;
            color: #6b7280;
        }
        table.galeri-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        table.galeri-grid td {
            padding: 6px 6px;
            vertical-align: top;
            text-align: center;
            border: none;
        }
        .galeri-img-wrap {
            border: 1px solid #d1d5db;
            padding: 4px;
            background: #fafafa;
            width: 100%;
            height: 180px;
            overflow: hidden;
            box-sizing: border-box;
            text-align: center;
        }
        .galeri-img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            max-height: 168px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .galeri-title {
            font-size: 10px;
            font-weight: bold;
            margin-top: 6px;
            color: #111827;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Gambar Kegiatan Posyandu</div>
        <div class="subtitle">{{ $posyandu->nama_posyandu }}</div>
        <div class="meta">Periode: {{ $periodeLabel ?? 'Semua Periode' }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td>Nama Posyandu:</td>
            <td>{{ $posyandu->nama_posyandu }}</td>
        </tr>
        <tr>
            <td>Alamat Posyandu:</td>
            <td>{{ $posyandu->alamat_posyandu ?? '-' }}</td>
        </tr>
        <tr>
            <td>Periode:</td>
            <td>{{ $periodeLabel ?? 'Semua Periode' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cetak:</td>
            <td>{{ $generatedAt->format('d F Y, H:i:s') }} WIB</td>
        </tr>
        <tr>
            <td>Dicetak Oleh:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td>Total Foto:</td>
            <td>{{ count($galeriFotos ?? []) }} foto</td>
        </tr>
    </table>

    @if (!empty($galeriFotos))
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
                            <img src="{{ $foto['data_uri'] }}" alt="{{ $foto['caption'] ?? 'Foto kegiatan' }}" class="galeri-img">
                        </div>
                        <div class="galeri-title">{{ trim((string) ($foto['caption'] ?? '')) !== '' ? $foto['caption'] : '-' }}</div>
                    </td>
                @endforeach
                @for ($pad = count($fotoRow); $pad < $galeriMaxKolom; $pad++)
                    <td></td>
                @endfor
            </tr>
            @endforeach
        </table>
    @else
        <div class="empty">
            <p>Tidak ada gambar kegiatan untuk periode yang dipilih.</p>
        </div>
    @endif
</body>
</html>
