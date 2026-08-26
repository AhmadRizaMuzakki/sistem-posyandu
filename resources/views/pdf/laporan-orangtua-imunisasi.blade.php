@php
    use Carbon\Carbon;
    $includeAnalytics = $includeAnalytics ?? false;
    $penilaianList = $penilaianList ?? [];
    $grafikChartUri = $grafikChartUri ?? null;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Imunisasi - {{ $user->name ?? 'Orangtua' }}</title>
    <style>
        @page { margin: 2cm 3cm; }
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
        body { margin: 0; color: #111827; }
        h1, h2, h3, p { margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 8px; }
        .title { font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 11px; margin-top: 2px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta-table td { padding: 2px 4px; vertical-align: top; }
        .meta-label { width: 120px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data thead { display: table-header-group; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 3px 4px; word-wrap: break-word; }
        table.data th { background: #f3f4f6; font-weight: bold; text-align: center; font-size: 8px; }
        table.data td { font-size: 8px; }
        .text-center { text-align: center; }
        .mt-1 { margin-top: 4px; }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0 4px;
            padding-bottom: 2px;
            border-bottom: 1px solid #d1d5db;
        }
        .chart-note { font-size: 8px; color: #6b7280; margin: 0 0 3px; }
        .chart-img { width: 100%; height: auto; display: block; }
        .keep-block {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .keep-block td { vertical-align: top; }
        .card {
            width: 100%;
            border: 1px solid #d1d5db;
            border-collapse: collapse;
            margin-top: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .card td { vertical-align: top; }
        .card-head {
            padding: 5px 7px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        .card-title { font-size: 10px; font-weight: bold; }
        .card-sub { font-size: 8px; color: #6b7280; margin-top: 1px; }
        .card-body { padding: 5px 7px; }
        .stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .stats td {
            width: 25%;
            padding: 3px 4px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .stat-label { font-size: 7px; color: #9ca3af; text-transform: uppercase; line-height: 1.2; }
        .stat-value { font-size: 8px; font-weight: bold; margin-top: 1px; line-height: 1.25; }
        .indeks {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .indeks th, .indeks td {
            border: 1px solid #e5e7eb;
            padding: 3px 4px;
            font-size: 8px;
            vertical-align: top;
        }
        .indeks tr { page-break-inside: avoid; break-inside: avoid; }
        .indeks th { background: #f3f4f6; text-align: left; font-weight: bold; }
        .indeks .col-i { width: 16%; }
        .indeks .col-s { width: 24%; }
        .badge {
            display: inline-block;
            padding: 0 3px;
            border-radius: 4px;
            font-size: 5.5px;
            font-weight: 600;
            color: #fff;
            line-height: 1.25;
        }
        .badge-green { background: #10b981; }
        .badge-red { background: #f43f5e; }
        .kesimpulan {
            margin-top: 5px;
            padding: 4px 6px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 8px;
            line-height: 1.35;
            page-break-inside: avoid;
        }
        .kesimpulan .muted { color: #6b7280; font-size: 7px; margin-top: 2px; }
        .page-break { page-break-before: always; break-before: page; }
        .section-break { page-break-before: always; break-before: page; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Imunisasi</div>
        <div class="subtitle">{{ $user->name ?? '-' }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Dicetak oleh</td>
            <td>: {{ $user->name ?? '-' }}</td>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ $generatedAt->format('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Data</td>
            <td>: {{ count($rows) }} imunisasi</td>
            <td class="meta-label">Filter Nama</td>
            <td>: {{ $filterNama ?: 'Semua' }}</td>
        </tr>
        @if(!empty($periodeLabel))
            <tr>
                <td class="meta-label">Periode</td>
                <td colspan="3">: {{ $periodeLabel }}</td>
            </tr>
        @endif
    </table>

    <h3 class="section-title">Daftar Imunisasi</h3>

    @if ($rows->isEmpty())
        <p class="mt-1">Tidak ada data imunisasi untuk filter yang dipilih.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Sasaran</th>
                    <th>Kategori</th>
                    <th>Jenis Imunisasi</th>
                    <th>Tanggal</th>
                    <th>Tinggi (cm)</th>
                    <th>Berat (kg)</th>
                    <th>Tekanan Darah</th>
                    <th>Gula Darah</th>
                    <th>Status Stunting</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr style="page-break-inside: avoid;">
                        <td class="text-center">{{ $row->no }}</td>
                        <td>{{ $row->nama_sasaran }}</td>
                        <td class="text-center">{{ $row->kategori_sasaran }}</td>
                        <td>{{ $row->jenis_imunisasi }}</td>
                        <td class="text-center">{{ $row->tanggal_imunisasi }}</td>
                        <td class="text-center">{{ $row->tinggi_badan }}</td>
                        <td class="text-center">{{ $row->berat_badan }}</td>
                        <td class="text-center">{{ $row->tekanan_darah ?? '-' }}</td>
                        <td class="text-center">{{ $row->gula_darah ?? '-' }}</td>
                        <td class="text-center">{{ $row->status_stunting ?? '-' }}</td>
                        <td>{{ $row->keterangan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($includeAnalytics)
        <div class="page-break"></div>

        <table class="keep-block">
            <tr>
                <td>
                    <h3 class="section-title" style="margin-top:0;">Grafik Pertumbuhan</h3>
                    @if(!empty($grafikChartUri))
                        @php $grafik = ($grafikPertumbuhan[0] ?? null); @endphp
                        <p class="chart-note">
                            {{ $grafik['nama'] ?? $filterNama }}
                            @if(!empty($grafik['kategori'])) — {{ $grafik['kategori'] }} @endif
                            @if(!empty($periodeLabel)) · {{ $periodeLabel }} @endif
                        </p>
                        <img class="chart-img" src="{{ $grafikChartUri }}" alt="Grafik Pertumbuhan">
                    @else
                        <p class="mt-1">Grafik belum tersedia (data tinggi/berat belum lengkap).</p>
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-break"></div>

        <h3 class="section-title" style="margin-top:0;">Hasil Penilaian</h3>
        @forelse($penilaianList as $item)
            @php
                $stuntingClass = ($item['status_stunting'] ?? '') === 'Stunting' ? 'badge-red' : 'badge-green';
            @endphp
            <table class="card">
                <tr>
                    <td class="card-head">
                        <div class="card-title">
                            {{ $item['nama'] }}
                            <span style="font-weight:normal;color:#6b7280;font-size:8px;">({{ $item['kategori'] }})</span>
                        </div>
                        <div class="card-sub">
                            @if(!empty($item['tanggal_kunjungan']))
                                {{ $item['tanggal_kunjungan'] }}
                                @if(!empty($item['jenis_imunisasi'])) — {{ $item['jenis_imunisasi'] }} @endif
                            @else
                                Pengukuran terakhir
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="card-body">
                        <table class="stats">
                            <tr>
                                <td>
                                    <div class="stat-label">Jenis Kelamin</div>
                                    <div class="stat-value">{{ $item['jenis_kelamin'] ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="stat-label">Tanggal Lahir</div>
                                    <div class="stat-value">{{ $item['tanggal_lahir'] ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="stat-label">Umur</div>
                                    <div class="stat-value">{{ $item['umur_label'] ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="stat-label">Status Stunting</div>
                                    <div class="stat-value">
                                        @if(!empty($item['status_stunting']))
                                            <span class="badge {{ $stuntingClass }}">{{ $item['status_stunting'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="stat-label">Berat Badan</div>
                                    <div class="stat-value">
                                        {{ isset($item['berat_badan']) && $item['berat_badan'] !== null ? number_format((float) $item['berat_badan'], 1, ',', '.') . ' kg' : '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="stat-label">Tinggi Badan</div>
                                    <div class="stat-value">
                                        {{ isset($item['tinggi_badan']) && $item['tinggi_badan'] !== null ? number_format((float) $item['tinggi_badan'], 1, ',', '.') . ' cm' : '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="stat-label">Tekanan Darah</div>
                                    <div class="stat-value">{{ !empty($item['tekanan_darah']) ? $item['tekanan_darah'] . ' mmHg' : '-' }}</div>
                                </td>
                                <td>
                                    <div class="stat-label">Gula Darah</div>
                                    <div class="stat-value">
                                        {{ isset($item['gula_darah']) && $item['gula_darah'] !== null ? number_format((float) $item['gula_darah'], 0, ',', '.') . ' mg/dL' : '-' }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        @if(!empty($item['indeks']))
                            <table class="indeks">
                                <thead>
                                    <tr>
                                        <th class="col-i">Indeks</th>
                                        <th class="col-s">Status</th>
                                        <th>Rekomendasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item['indeks'] as $indeks)
                                        <tr>
                                            <td>
                                                <strong>{{ $indeks['singkat'] ?? '-' }}</strong><br>
                                                <span style="font-size:7px;color:#6b7280;">{{ $indeks['nama'] ?? '' }}</span>
                                            </td>
                                            <td>{{ $indeks['status'] ?? '-' }}</td>
                                            <td>{{ $indeks['saran'] ?? ($indeks['rekomendasi'] ?? '-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if(!empty($item['kesimpulan']) || !empty($item['status_stunting']) || !empty($item['penjelasan']))
                            <div class="kesimpulan">
                                @if(!empty($item['kesimpulan']))
                                    <strong>Kesimpulan:</strong> {{ $item['kesimpulan'] }}
                                    @if(!empty($item['status_stunting']))
                                        &nbsp;|&nbsp; <strong>Status Stunting:</strong>
                                        <span class="badge {{ $stuntingClass }}">{{ $item['status_stunting'] }}</span>
                                    @endif
                                @elseif(!empty($item['status_stunting']))
                                    <strong>Status Stunting:</strong>
                                    <span class="badge {{ $stuntingClass }}">{{ $item['status_stunting'] }}</span>
                                @endif
                                @if(!empty($item['penjelasan']))
                                    <div class="muted">{{ $item['penjelasan'] }}</div>
                                @endif
                            </div>
                        @elseif(empty($item['indeks']))
                            <p style="font-size:8px;color:#6b7280;">Data pengukuran belum lengkap untuk penilaian.</p>
                        @endif
                    </td>
                </tr>
            </table>
        @empty
            <p class="mt-1">Hasil penilaian belum tersedia untuk sasaran terpilih.</p>
        @endforelse
    @endif
</body>
</html>
