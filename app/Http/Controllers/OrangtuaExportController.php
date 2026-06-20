<?php

namespace App\Http\Controllers;

use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranDewasa;
use App\Models\SasaranLansia;
use App\Models\SasaranPralansia;
use App\Models\SasaranRemaja;
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
        $imunisasiList = $this->buildImunisasiList($filterNama);

        $rows = collect();
        $no = 1;
        foreach ($imunisasiList as $item) {
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
                    'keterangan' => $im->keterangan ?? '-',
                ]);
            }
        }

        $user = Auth::user();
        $filename = 'Laporan_Imunisasi_' . date('Y-m-d_His') . '.pdf';

        return $this->renderPdf('pdf.laporan-orangtua-imunisasi', [
            'rows' => $rows,
            'user' => $user,
            'generatedAt' => now('Asia/Jakarta'),
            'filterNama' => $filterNama,
        ], $filename);
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

    private function buildImunisasiList(string $filterNama): Collection
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

    private function renderPdf(string $view, array $data, string $fileName, string $orientation = 'portrait'): Response
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

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
