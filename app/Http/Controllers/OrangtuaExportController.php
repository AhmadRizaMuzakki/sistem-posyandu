<?php

namespace App\Http\Controllers;

use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
use App\Services\AntropometriService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class OrangtuaExportController extends Controller
{
    public function keluargaPdf(): Response
    {
        $user = Auth::user();
        $noKk = $this->resolveNoKk();
        $allKeluarga = $this->buildKeluargaData($noKk);

        $userName = mb_convert_encoding($user->name ?? '', 'UTF-8', 'UTF-8');
        $filename = 'Data_Keluarga_' . date('Y-m-d_His') . '.pdf';

        return $this->renderPdf('pdf.data-keluarga', [
            'allKeluarga' => $allKeluarga,
            'user' => (object) ['name' => $userName],
            'noKk' => $noKk,
            'generatedAt' => now('Asia/Jakarta'),
        ], $filename, 'landscape');
    }

    public function imunisasiPdf(Request $request): Response
    {
        $filterNama = trim((string) $request->query('sasaran', ''));
        $filterBulan = trim((string) $request->query('bulan', ''));
        $filterTahun = trim((string) $request->query('tahun', ''));
        $imunisasiList = $this->buildImunisasiList($filterNama, $filterBulan, $filterTahun);

        $rows = collect();
        $no = 1;
        $antropometri = app(AntropometriService::class);
        foreach ($imunisasiList as $item) {
            $tanggalLahir = ! empty($item['sasaran']['tanggal_lahir'] ?? null)
                ? Carbon::parse($item['sasaran']['tanggal_lahir'])
                : null;
            $jenisKelamin = $item['sasaran']['jenis_kelamin'] ?? null;

            foreach ($item['imunisasi'] as $im) {
                $rows->push((object) [
                    'no' => $no++,
                    'nama_sasaran' => $item['sasaran']['nama'] ?? '-',
                    'kategori_sasaran' => $this->kategoriLabel($item['sasaran']['kategori'] ?? ''),
                    'jenis_imunisasi' => $im->jenis_imunisasi ?? '-',
                    'tanggal_imunisasi' => $im->tanggal_imunisasi ? $im->tanggal_imunisasi->format('d/m/Y') : '-',
                    'tinggi_badan' => $im->tinggi_badan !== null ? number_format($im->tinggi_badan, 1, ',', '.') : '-',
                    'berat_badan' => $im->berat_badan !== null ? number_format($im->berat_badan, 1, ',', '.') : '-',
                    'tekanan_darah' => $im->tekanan_darah ? $im->tekanan_darah . ' mmHg' : '-',
                    'gula_darah' => $im->gula_darah !== null ? number_format($im->gula_darah, 0, ',', '.') . ' mg/dL' : '-',
                    'status_stunting' => $antropometri->labelStatusStunting(
                        $im->berat_badan !== null ? (float) $im->berat_badan : null,
                        $im->tinggi_badan !== null ? (float) $im->tinggi_badan : null,
                        $tanggalLahir,
                        $im->tanggal_imunisasi ? Carbon::parse($im->tanggal_imunisasi) : null,
                        $jenisKelamin,
                        $im->tekanan_darah,
                        $im->gula_darah !== null ? (float) $im->gula_darah : null,
                        $item['sasaran']['kategori'] ?? $im->kategori_sasaran ?? null
                    ),
                    'keterangan' => $im->keterangan ?? '-',
                ]);
            }
        }

        $grafikPertumbuhan = [];
        $penilaianList = [];
        $grafikChartUri = null;

        if ($filterNama !== '') {
            $analytics = $this->buildImunisasiAnalytics($imunisasiList, $antropometri);
            $grafikPertumbuhan = $analytics['grafikPertumbuhan'];
            $penilaianList = $analytics['penilaianList'];

            $grafik = $grafikPertumbuhan[0] ?? null;
            if ($grafik) {
                $grafikChartUri = \App\Helpers\PdfChartHelper::pertumbuhanChartDataUri(
                    $grafik['labels'] ?? [],
                    $grafik['tinggi'] ?? [],
                    $grafik['berat'] ?? [],
                    760,
                    220
                );
            }
        }

        $user = Auth::user();
        $filename = 'Laporan_Imunisasi_' . date('Y-m-d_His') . '.pdf';
        $periodeLabel = $this->formatPeriodeLabel($filterBulan, $filterTahun);

        return $this->renderPdf('pdf.laporan-orangtua-imunisasi', [
            'rows' => $rows,
            'user' => $user,
            'generatedAt' => now('Asia/Jakarta'),
            'filterNama' => $filterNama,
            'filterBulan' => $filterBulan,
            'filterTahun' => $filterTahun,
            'periodeLabel' => $periodeLabel,
            'grafikPertumbuhan' => $grafikPertumbuhan,
            'grafikChartUri' => $grafikChartUri,
            'penilaianList' => $penilaianList,
            'includeAnalytics' => $filterNama !== '',
        ], $filename, 'landscape');
    }

    private function resolveNoKk(): ?string
    {
        $user = Auth::user();
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            return str_replace('@gmail.com', '', $user->email);
        }

        return null;
    }

    private function buildKeluargaData(?string $noKk): Collection
    {
        $allKeluarga = collect();

        if (! $noKk) {
            return $allKeluarga;
        }

        $sasaranBayi = SasaranBayibalita::where('no_kk_sasaran', $noKk)->get();
        $sasaranRemaja = SasaranRemaja::where('no_kk_sasaran', $noKk)->get();
        $sasaranDewasa = SasaranDewasa::where('no_kk_sasaran', $noKk)->get();
        $sasaranPralansia = SasaranPralansia::where('no_kk_sasaran', $noKk)->get();
        $sasaranLansia = SasaranLansia::where('no_kk_sasaran', $noKk)->get();

        $no = 1;
        foreach ($sasaranBayi as $sasaran) {
            $allKeluarga->push($this->formatKeluargaRow($sasaran, $no++, 'Bayi/Balita'));
        }
        foreach ($sasaranRemaja as $sasaran) {
            $allKeluarga->push($this->formatKeluargaRow($sasaran, $no++, 'Remaja'));
        }
        foreach ($sasaranDewasa as $sasaran) {
            $allKeluarga->push($this->formatKeluargaRow($sasaran, $no++, 'Dewasa'));
        }
        foreach ($sasaranPralansia as $sasaran) {
            $allKeluarga->push($this->formatKeluargaRow($sasaran, $no++, 'Pralansia'));
        }
        foreach ($sasaranLansia as $sasaran) {
            $allKeluarga->push($this->formatKeluargaRow($sasaran, $no++, 'Lansia'));
        }

        return $allKeluarga;
    }

    private function formatKeluargaRow(object $sasaran, int $no, string $kategori): array
    {
        return [
            'no' => $no,
            'nama' => mb_convert_encoding($sasaran->nama_sasaran ?? '', 'UTF-8', 'UTF-8'),
            'nik' => $sasaran->nik_sasaran ?? '',
            'kategori' => $kategori,
            'tanggal_lahir' => $sasaran->tanggal_lahir ? Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
            'umur' => $sasaran->umur_sasaran ? $sasaran->umur_sasaran . ' tahun' : '-',
            'jenis_kelamin' => mb_convert_encoding($sasaran->jenis_kelamin ?? '-', 'UTF-8', 'UTF-8'),
            'alamat' => mb_convert_encoding($sasaran->alamat_sasaran ?? '-', 'UTF-8', 'UTF-8'),
        ];
    }

    private function buildImunisasiList(string $filterNama, string $filterBulan = '', string $filterTahun = ''): Collection
    {
        $noKk = $this->resolveNoKk();
        $allSasaran = collect();

        if ($noKk) {
            $kategoriMap = [
                ['model' => SasaranBayibalita::class, 'id' => 'id_sasaran_bayibalita', 'slug' => 'bayibalita'],
                ['model' => SasaranRemaja::class, 'id' => 'id_sasaran_remaja', 'slug' => 'remaja'],
                ['model' => SasaranDewasa::class, 'id' => 'id_sasaran_dewasa', 'slug' => 'dewasa'],
                ['model' => SasaranPralansia::class, 'id' => 'id_sasaran_pralansia', 'slug' => 'pralansia'],
                ['model' => SasaranLansia::class, 'id' => 'id_sasaran_lansia', 'slug' => 'lansia'],
            ];

            foreach ($kategoriMap as $cfg) {
                $sasaranList = $cfg['model']::where('no_kk_sasaran', $noKk)->get();
                foreach ($sasaranList as $s) {
                    $allSasaran->push([
                        'id' => $s->{$cfg['id']},
                        'kategori' => $cfg['slug'],
                        'nama' => $s->nama_sasaran,
                        'nik' => $s->nik_sasaran,
                        'tanggal_lahir' => $s->tanggal_lahir,
                        'jenis_kelamin' => $s->jenis_kelamin,
                    ]);
                }
            }
        }

        $sasaranConditions = $allSasaran->map(fn ($s) => [
            'id' => $s['id'],
            'kategori' => $s['kategori'],
        ])->toArray();

        $allImunisasi = collect();
        if (! empty($sasaranConditions)) {
            $query = Imunisasi::where(function ($q) use ($sasaranConditions) {
                foreach ($sasaranConditions as $cond) {
                    $q->orWhere(function ($subQ) use ($cond) {
                        $subQ->where('id_sasaran', $cond['id'])
                            ->where('kategori_sasaran', $cond['kategori']);
                    });
                }
            });

            if ($filterBulan !== '' && is_numeric($filterBulan) && (int) $filterBulan >= 1 && (int) $filterBulan <= 12) {
                $query->whereMonth('tanggal_imunisasi', (int) $filterBulan);
            }
            if ($filterTahun !== '' && is_numeric($filterTahun) && (int) $filterTahun >= 2000 && (int) $filterTahun <= 2100) {
                $query->whereYear('tanggal_imunisasi', (int) $filterTahun);
            }

            $allImunisasi = $query->orderBy('tanggal_imunisasi', 'desc')->get();
        }

        $groupedImunisasi = $allImunisasi->groupBy(fn ($im) => $im->kategori_sasaran . '_' . $im->id_sasaran);

        $imunisasiList = collect();
        foreach ($allSasaran as $sasaran) {
            if ($filterNama !== '' && trim($sasaran['nama']) !== trim($filterNama)) {
                continue;
            }

            $key = $sasaran['kategori'] . '_' . $sasaran['id'];
            $imunisasi = $groupedImunisasi->get($key, collect());

            if ($imunisasi->count() > 0) {
                $imunisasiList->push(['sasaran' => $sasaran, 'imunisasi' => $imunisasi]);
            }
        }

        return $imunisasiList;
    }

    private function kategoriLabel(string $slug): string
    {
        return match ($slug) {
            'bayibalita' => 'Bayi/Balita',
            'remaja' => 'Remaja',
            'dewasa' => 'Dewasa',
            'pralansia' => 'Pralansia',
            'lansia' => 'Lansia',
            default => ucfirst($slug ?: '-'),
        };
    }

    private function formatPeriodeLabel(string $bulan, string $tahun): ?string
    {
        $bulanValid = $bulan !== '' && is_numeric($bulan) && (int) $bulan >= 1 && (int) $bulan <= 12;
        $tahunValid = $tahun !== '' && is_numeric($tahun);

        if ($bulanValid && $tahunValid) {
            return Carbon::create((int) $tahun, (int) $bulan, 1)->locale('id')->translatedFormat('F Y');
        }
        if ($bulanValid) {
            return Carbon::create(now()->year, (int) $bulan, 1)->locale('id')->translatedFormat('F');
        }
        if ($tahunValid) {
            return (string) (int) $tahun;
        }

        return null;
    }

    /**
     * @param  Collection<int, array{sasaran: array<string, mixed>, imunisasi: Collection}>  $imunisasiList
     * @return array{grafikPertumbuhan: array<int, array<string, mixed>>, penilaianList: array<int, array<string, mixed>>}
     */
    private function buildImunisasiAnalytics(Collection $imunisasiList, AntropometriService $antropometri): array
    {
        $grafikPertumbuhan = [];
        $penilaianList = [];

        foreach ($imunisasiList as $item) {
            $sasaran = $item['sasaran'];
            $records = $item['imunisasi']->sortBy('tanggal_imunisasi')->values();
            $tanggalLahir = ! empty($sasaran['tanggal_lahir'])
                ? Carbon::parse($sasaran['tanggal_lahir'])
                : null;

            $labels = [];
            $berat = [];
            $tinggi = [];

            foreach ($records as $im) {
                if ($im->tanggal_imunisasi === null) {
                    continue;
                }
                $labels[] = $im->tanggal_imunisasi->format('d/m/Y');
                $berat[] = $im->berat_badan !== null ? (float) $im->berat_badan : null;
                $tinggi[] = $im->tinggi_badan !== null ? (float) $im->tinggi_badan : null;
            }

            if (count($labels) > 0) {
                $grafikPertumbuhan[] = [
                    'nama' => $sasaran['nama'] ?? '-',
                    'kategori' => $this->kategoriLabel($sasaran['kategori'] ?? ''),
                    'labels' => $labels,
                    'berat' => $berat,
                    'tinggi' => $tinggi,
                ];
            }

            $terakhir = $records->sortByDesc('tanggal_imunisasi')->first();
            $penilaian = null;
            if ($terakhir) {
                if ($terakhir->berat_badan !== null && $terakhir->tinggi_badan !== null) {
                    $penilaian = $antropometri->evaluasi(
                        (float) $terakhir->berat_badan,
                        (float) $terakhir->tinggi_badan,
                        $tanggalLahir,
                        $terakhir->tanggal_imunisasi ? Carbon::parse($terakhir->tanggal_imunisasi) : null,
                        $sasaran['jenis_kelamin'] ?? null,
                        $terakhir->tekanan_darah,
                        $terakhir->gula_darah !== null ? (float) $terakhir->gula_darah : null
                    );
                } elseif ($terakhir->tekanan_darah || $terakhir->gula_darah !== null) {
                    $penilaian = $antropometri->evaluasiTandaVital(
                        $terakhir->tekanan_darah,
                        $terakhir->gula_darah !== null ? (float) $terakhir->gula_darah : null
                    );
                }
            }

            $statusStunting = null;
            if ($terakhir) {
                $statusStunting = $antropometri->labelStatusStunting(
                    $terakhir->berat_badan !== null ? (float) $terakhir->berat_badan : null,
                    $terakhir->tinggi_badan !== null ? (float) $terakhir->tinggi_badan : null,
                    $tanggalLahir,
                    $terakhir->tanggal_imunisasi ? Carbon::parse($terakhir->tanggal_imunisasi) : null,
                    $sasaran['jenis_kelamin'] ?? null,
                    $terakhir->tekanan_darah,
                    $terakhir->gula_darah !== null ? (float) $terakhir->gula_darah : null,
                    $sasaran['kategori'] ?? $terakhir->kategori_sasaran ?? null
                );
                if ($statusStunting === '-') {
                    $statusStunting = $penilaian['status_stunting'] ?? ($penilaian['card']['status_stunting'] ?? null);
                }
            }

            $card = $penilaian['card'] ?? [];
            $penilaianList[] = [
                'nama' => $sasaran['nama'] ?? '-',
                'kategori' => $this->kategoriLabel($sasaran['kategori'] ?? ''),
                'nik' => $sasaran['nik'] ?? '-',
                'tanggal_lahir' => $tanggalLahir?->format('d/m/Y') ?? '-',
                'jenis_kelamin' => $card['jenis_kelamin'] ?? ($sasaran['jenis_kelamin'] ?? '-'),
                'umur_label' => isset($card['umur_bulan'])
                    ? $card['umur_bulan'] . ' Bulan'
                    : ($card['umur_label'] ?? ($penilaian['umur_label'] ?? '-')),
                'tanggal_kunjungan' => $terakhir?->tanggal_imunisasi?->format('d/m/Y'),
                'jenis_imunisasi' => $terakhir?->jenis_imunisasi,
                'berat_badan' => $card['berat_badan'] ?? $terakhir?->berat_badan,
                'tinggi_badan' => $card['tinggi_badan'] ?? $terakhir?->tinggi_badan,
                'tekanan_darah' => $card['tekanan_darah'] ?? $terakhir?->tekanan_darah,
                'gula_darah' => $card['gula_darah'] ?? $terakhir?->gula_darah,
                'indeks' => $card['indeks'] ?? ($penilaian['indeks'] ?? []),
                'kesimpulan' => $card['kesimpulan'] ?? ($penilaian['kategori'] ?? null),
                'penjelasan' => $penilaian['penjelasan'] ?? null,
                'status_stunting' => ($statusStunting && $statusStunting !== '-') ? $statusStunting : null,
            ];
        }

        return [
            'grafikPertumbuhan' => $grafikPertumbuhan,
            'penilaianList' => $penilaianList,
        ];
    }

    private function renderPdf(string $view, array $data, string $fileName, string $orientation = 'portrait'): Response
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 96);
        $dompdf = new Dompdf($options);

        $html = view($view, $data)->render();
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
