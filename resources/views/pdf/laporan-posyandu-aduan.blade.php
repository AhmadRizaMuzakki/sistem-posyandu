@php
    use App\Helpers\AduanOptions;

    $bidang = $kategori ?? '';
    $bidangLabel = $bidangLabel ?? AduanOptions::kategoriLabel($bidang);
    $tahunFooter = $generatedAt->format('Y');
    $minRows = 10;
    $rowCount = max($aduanList->count(), $minRows);

    $isTl = function (?string $status): bool {
        return in_array($status, [AduanOptions::STATUS_DIPROSES, AduanOptions::STATUS_SELESAI], true);
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Format Pencatatan SPM Posyandu Bidang {{ $bidangLabel }}</title>
    <style>
        @page { margin: 12mm 12mm; }
        * { font-family: DejaVu Sans, Arial, sans-serif; }
        body { margin: 0; color: #000; font-size: 11px; }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 14px 0;
        }
        .info {
            width: 55%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .info td {
            padding: 2px 0;
            vertical-align: bottom;
            font-size: 11px;
        }
        .info .label { width: 120px; white-space: nowrap; }
        .info .colon { width: 12px; text-align: center; }
        .info .value {
            border-bottom: 1px solid #000;
            min-width: 180px;
            padding-left: 4px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
            font-size: 10px;
        }
        table.data th {
            font-weight: bold;
            text-align: center;
            background: #fff;
        }
        table.data .num { text-align: center; width: 28px; }
        table.data .center { text-align: center; }
        table.data .cell-empty { height: 22px; }
        .sign-wrap { margin-top: 28px; width: 100%; }
        table.sign {
            width: 100%;
            border-collapse: collapse;
        }
        table.sign td {
            border: none;
            width: 50%;
            vertical-align: top;
            font-size: 11px;
        }
        .sign-left { text-align: left; padding-left: 24px; }
        .sign-right { text-align: center; }
        .sign-space { height: 55px; }
        .muted { color: #444; font-size: 9px; margin-top: 10px; text-align: right; }
    </style>
</head>
<body>
    <div class="title">Format Pencatatan SPM Posyandu Bidang {{ $bidangLabel }}</div>

    <table class="info">
        <tr>
            <td class="label">Posyandu</td>
            <td class="colon">:</td>
            <td class="value">{{ $posyandu->nama_posyandu }}</td>
        </tr>
        <tr>
            <td class="label">Kelurahan/Desa</td>
            <td class="colon">:</td>
            <td class="value">{{ $kelurahanDesa ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Kecamatan</td>
            <td class="colon">:</td>
            <td class="value">{{ $kecamatan ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Kabupaten/Kota</td>
            <td class="colon">:</td>
            <td class="value">{{ $kabupatenKota ?? '' }}</td>
        </tr>
    </table>

    @if($bidang === AduanOptions::SPM_PEKERJAAN_UMUM)
        {{-- Template: Pekerjaan Umum --}}
        <table class="data">
            <thead>
                <tr>
                    <th rowspan="2" class="num">No</th>
                    <th rowspan="2" style="width:9%">Tanggal</th>
                    <th rowspan="2" style="width:14%">No Surat Permohonan RT</th>
                    <th rowspan="2" style="width:14%">Nama</th>
                    <th rowspan="2" style="width:20%">Keluhan</th>
                    <th rowspan="2" style="width:18%">Lokasi Pembangunan Sarana</th>
                    <th colspan="2" style="width:14%">Tindaklanjut</th>
                </tr>
                <tr>
                    <th style="width:7%">TL</th>
                    <th style="width:7%">BTL</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $rowCount; $i++)
                    @php
                        $aduan = $aduanList->values()->get($i);
                        $orangtua = $aduan ? $orangtuaMap->get((string) $aduan->no_kk) : null;
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="center">{{ $aduan?->tanggal_aduan?->format('d/m/Y') }}</td>
                        <td class="center">{{ $aduan?->no_surat_permohonan_rt }}</td>
                        <td>{{ $orangtua?->nama }}</td>
                        <td>{{ $aduan?->isi_aduan ?? $aduan?->judul }}</td>
                        <td>{{ $orangtua?->alamat }}</td>
                        <td class="center">{{ $aduan && $isTl($aduan->status) ? 'V' : '' }}</td>
                        <td class="center">{{ $aduan && ! $isTl($aduan->status) ? 'V' : '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

    @elseif($bidang === AduanOptions::SPM_PERUMAHAN_RAKYAT)
        {{-- Template: Perumahan Rakyat --}}
        <table class="data">
            <thead>
                <tr>
                    <th rowspan="2" class="num">No</th>
                    <th rowspan="2" style="width:8%">Tanggal</th>
                    <th rowspan="2" style="width:11%">NIK</th>
                    <th rowspan="2" style="width:12%">Nama</th>
                    <th colspan="5" style="width:40%">Persyaratan</th>
                    <th colspan="2" style="width:12%">Tindaklanjut</th>
                    <th rowspan="2" style="width:10%">Ket.</th>
                </tr>
                <tr>
                    <th style="width:7%; font-size:8px;">FC KK</th>
                    <th style="width:7%; font-size:8px;">FC KTP</th>
                    <th style="width:6%; font-size:8px;">SP*</th>
                    <th style="width:10%; font-size:8px;">Suket Penghasilan</th>
                    <th style="width:10%; font-size:8px;">Foto Kondisi Rumah</th>
                    <th style="width:6%">TL</th>
                    <th style="width:6%">BTL</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $rowCount; $i++)
                    @php
                        $aduan = $aduanList->values()->get($i);
                        $orangtua = $aduan ? $orangtuaMap->get((string) $aduan->no_kk) : null;
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="center">{{ $aduan?->tanggal_aduan?->format('d/m/Y') }}</td>
                        <td class="center">{{ $orangtua?->nik }}</td>
                        <td>{{ $orangtua?->nama }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="center">{{ $aduan && $isTl($aduan->status) ? 'V' : '' }}</td>
                        <td class="center">{{ $aduan && ! $isTl($aduan->status) ? 'V' : '' }}</td>
                        <td>{{ $aduan?->tanggapan }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

    @else
        {{-- Template default: Trantibum Linmas & Pendidikan (No, Tanggal, NIK, Nama, Hal Pengaduan, Keterangan TL/BTL) --}}
        <table class="data">
            <thead>
                <tr>
                    <th rowspan="2" class="num">No</th>
                    <th rowspan="2" style="width:12%">Tanggal</th>
                    <th rowspan="2" style="width:16%">NIK</th>
                    <th rowspan="2" style="width:18%">Nama</th>
                    <th rowspan="2" style="width:34%">Hal Pengaduan</th>
                    <th colspan="2" style="width:14%">Keterangan</th>
                </tr>
                <tr>
                    <th style="width:7%">TL</th>
                    <th style="width:7%">BTL</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $rowCount; $i++)
                    @php
                        $aduan = $aduanList->values()->get($i);
                        $orangtua = $aduan ? $orangtuaMap->get((string) $aduan->no_kk) : null;
                        $halPengaduan = $aduan
                            ? trim(($aduan->judul ?? '') . (($aduan->judul && $aduan->isi_aduan) ? ' — ' : '') . ($aduan->isi_aduan ?? ''))
                            : '';
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="center">{{ $aduan?->tanggal_aduan?->format('d/m/Y') }}</td>
                        <td class="center">{{ $orangtua?->nik }}</td>
                        <td>{{ $orangtua?->nama }}</td>
                        <td>{{ $halPengaduan }}</td>
                        <td class="center">{{ $aduan && $isTl($aduan->status) ? 'V' : '' }}</td>
                        <td class="center">{{ $aduan && ! $isTl($aduan->status) ? 'V' : '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    @endif

    <div class="sign-wrap">
        <table class="sign">
            <tr>
                <td class="sign-left">
                    <div>Mengetahui:</div>
                    <div>Kader Posyandu</div>
                    <div class="sign-space"></div>
                    <div>(................................)</div>
                </td>
                <td class="sign-right">
                    <div>..........{{ $tahunFooter }}</div>
                    <div>Kader Yang Melaporkan</div>
                    <div class="sign-space"></div>
                    <div>(................................)</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
