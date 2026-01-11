@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SK Posyandu - {{ $posyandu->nama_posyandu }}</title>
    <style>
        @page {
            margin: 20mm 20mm;
        }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
        }
        body {
            margin: 0;
            color: #111827;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e40af;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #4b5563;
            margin-top: 5px;
        }
        .content {
            margin-top: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            border-left: 4px solid #1e40af;
            padding-left: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 8px;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
            color: #374151;
        }
        .info-table td:last-child {
            color: #111827;
        }
        .statistics {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 15px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            margin: 0 5px;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        .kader-list {
            margin-top: 10px;
        }
        .kader-item {
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .generated-info {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Surat Keterangan Posyandu</div>
        <div class="subtitle">Sistem Informasi Posyandu</div>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">Informasi Posyandu</div>
            <table class="info-table">
                <tr>
                    <td>Nama Posyandu</td>
                    <td>: {{ $posyandu->nama_posyandu }}</td>
                </tr>
                @if($posyandu->alamat_posyandu)
                <tr>
                    <td>Alamat</td>
                    <td>: {{ $posyandu->alamat_posyandu }}</td>
                </tr>
                @endif
                @if($posyandu->domisili_posyandu)
                <tr>
                    <td>Domisili</td>
                    <td>: {{ $posyandu->domisili_posyandu }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tanggal Dibuat</td>
                    <td>: {{ $posyandu->created_at ? Carbon::parse($posyandu->created_at)->format('d F Y') : '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Statistik Posyandu</div>
            <div class="statistics">
                <div class="stat-item">
                    <div class="stat-label">Total Kader</div>
                    <div class="stat-value">{{ $totalKader }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Sasaran</div>
                    <div class="stat-value">{{ number_format($totalSasaran, 0, ',', '.') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Bayi/Balita</div>
                    <div class="stat-value">{{ $posyandu->sasaran_bayibalita->count() }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Remaja</div>
                    <div class="stat-value">{{ $posyandu->sasaran_remaja->count() }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Dewasa</div>
                    <div class="stat-value">{{ $posyandu->sasaran_dewasa->count() }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Ibu Hamil</div>
                    <div class="stat-value">{{ $posyandu->sasaran_ibuhamil->count() }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Pralansia</div>
                    <div class="stat-value">{{ $posyandu->sasaran_pralansia->count() }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Lansia</div>
                    <div class="stat-value">{{ $posyandu->sasaran_lansia->count() }}</div>
                </div>
            </div>
        </div>

        @if($posyandu->kader->count() > 0)
        <div class="section">
            <div class="section-title">Daftar Kader Posyandu</div>
            <div class="kader-list">
                @foreach($posyandu->kader as $index => $kader)
                <div class="kader-item">
                    <strong>{{ $index + 1 }}. {{ $kader->nama_kader }}</strong>
                    @if($kader->user)
                        <br><span style="font-size: 10px; color: #6b7280;">Email: {{ $kader->user->email }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="footer">
        <div>Dokumen ini dibuat secara otomatis oleh Sistem Informasi Posyandu</div>
        <div class="generated-info">
            Dicetak pada: {{ $generatedAt->format('d F Y, H:i:s') }} WIB
            @if($user)
                <br>Oleh: {{ $user->name }}
            @endif
        </div>
    </div>
</body>
</html>

