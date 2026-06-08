<?php

namespace App\Livewire\Orangtua;

use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

class OrangtuaDashboard extends Component
{
    #[Layout('layouts.orangtuadashboard')]

    protected function resolveNoKk(): ?string
    {
        $user = Auth::user();
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            return str_replace('@gmail.com', '', $user->email);
        }

        return null;
    }

    public function render()
    {
        $noKk = $this->resolveNoKk();
        $orangtua = $noKk ? Orangtua::where('no_kk', $noKk)->first() : null;
        $allKeluarga = collect();

        if ($noKk) {
            $sasaranBayi = SasaranBayibalita::where('no_kk_sasaran', $noKk)->get();
            $sasaranRemaja = SasaranRemaja::where('no_kk_sasaran', $noKk)->get();
            $sasaranDewasa = SasaranDewasa::where('no_kk_sasaran', $noKk)->get();
            $sasaranPralansia = SasaranPralansia::where('no_kk_sasaran', $noKk)->get();
            $sasaranLansia = SasaranLansia::where('no_kk_sasaran', $noKk)->get();

            foreach ($sasaranBayi as $sasaran) {
                $allKeluarga->push([
                    'id' => $sasaran->id_sasaran_bayibalita,
                    'kategori' => 'Bayi/Balita',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranRemaja as $sasaran) {
                $allKeluarga->push([
                    'id' => $sasaran->id_sasaran_remaja,
                    'kategori' => 'Remaja',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranDewasa as $sasaran) {
                $allKeluarga->push([
                    'id' => $sasaran->id_sasaran_dewasa,
                    'kategori' => 'Dewasa',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranPralansia as $sasaran) {
                $allKeluarga->push([
                    'id' => $sasaran->id_sasaran_pralansia,
                    'kategori' => 'Pralansia',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }

            foreach ($sasaranLansia as $sasaran) {
                $allKeluarga->push([
                    'id' => $sasaran->id_sasaran_lansia,
                    'kategori' => 'Lansia',
                    'nama' => $sasaran->nama_sasaran,
                    'nik' => $sasaran->nik_sasaran,
                    'tanggal_lahir' => $sasaran->tanggal_lahir,
                    'jenis_kelamin' => $sasaran->jenis_kelamin,
                    'umur' => $sasaran->umur_sasaran,
                    'alamat' => $sasaran->alamat_sasaran,
                ]);
            }
        }

        return view('livewire.orangtua.orang-tua', [
            'allKeluarga' => $allKeluarga,
            'orangtua' => $orangtua,
        ]);
    }

    /**
     * Export data keluarga ke PDF
     */
    public function exportKeluarga()
    {
        $user = Auth::user();
        $noKk = $this->resolveNoKk();
        $allKeluarga = collect();

        if ($noKk) {
            $sasaranBayi = SasaranBayibalita::where('no_kk_sasaran', $noKk)->get();
            $sasaranRemaja = SasaranRemaja::where('no_kk_sasaran', $noKk)->get();
            $sasaranDewasa = SasaranDewasa::where('no_kk_sasaran', $noKk)->get();
            $sasaranPralansia = SasaranPralansia::where('no_kk_sasaran', $noKk)->get();
            $sasaranLansia = SasaranLansia::where('no_kk_sasaran', $noKk)->get();

            $no = 1;
            foreach ($sasaranBayi as $sasaran) {
                $allKeluarga->push([
                    'no' => $no++,
                    'nama' => mb_convert_encoding($sasaran->nama_sasaran ?? '', 'UTF-8', 'UTF-8'),
                    'nik' => $sasaran->nik_sasaran ?? '',
                    'kategori' => 'Bayi/Balita',
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
                    'umur' => $sasaran->umur_sasaran ? $sasaran->umur_sasaran . ' tahun' : '-',
                    'jenis_kelamin' => mb_convert_encoding($sasaran->jenis_kelamin ?? '-', 'UTF-8', 'UTF-8'),
                    'alamat' => mb_convert_encoding($sasaran->alamat_sasaran ?? '-', 'UTF-8', 'UTF-8'),
                ]);
            }

            foreach ($sasaranRemaja as $sasaran) {
                $allKeluarga->push([
                    'no' => $no++,
                    'nama' => mb_convert_encoding($sasaran->nama_sasaran ?? '', 'UTF-8', 'UTF-8'),
                    'nik' => $sasaran->nik_sasaran ?? '',
                    'kategori' => 'Remaja',
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
                    'umur' => $sasaran->umur_sasaran ? $sasaran->umur_sasaran . ' tahun' : '-',
                    'jenis_kelamin' => mb_convert_encoding($sasaran->jenis_kelamin ?? '-', 'UTF-8', 'UTF-8'),
                    'alamat' => mb_convert_encoding($sasaran->alamat_sasaran ?? '-', 'UTF-8', 'UTF-8'),
                ]);
            }

            foreach ($sasaranDewasa as $sasaran) {
                $allKeluarga->push([
                    'no' => $no++,
                    'nama' => mb_convert_encoding($sasaran->nama_sasaran ?? '', 'UTF-8', 'UTF-8'),
                    'nik' => $sasaran->nik_sasaran ?? '',
                    'kategori' => 'Dewasa',
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
                    'umur' => $sasaran->umur_sasaran ? $sasaran->umur_sasaran . ' tahun' : '-',
                    'jenis_kelamin' => mb_convert_encoding($sasaran->jenis_kelamin ?? '-', 'UTF-8', 'UTF-8'),
                    'alamat' => mb_convert_encoding($sasaran->alamat_sasaran ?? '-', 'UTF-8', 'UTF-8'),
                ]);
            }

            foreach ($sasaranPralansia as $sasaran) {
                $allKeluarga->push([
                    'no' => $no++,
                    'nama' => mb_convert_encoding($sasaran->nama_sasaran ?? '', 'UTF-8', 'UTF-8'),
                    'nik' => $sasaran->nik_sasaran ?? '',
                    'kategori' => 'Pralansia',
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
                    'umur' => $sasaran->umur_sasaran ? $sasaran->umur_sasaran . ' tahun' : '-',
                    'jenis_kelamin' => mb_convert_encoding($sasaran->jenis_kelamin ?? '-', 'UTF-8', 'UTF-8'),
                    'alamat' => mb_convert_encoding($sasaran->alamat_sasaran ?? '-', 'UTF-8', 'UTF-8'),
                ]);
            }

            foreach ($sasaranLansia as $sasaran) {
                $allKeluarga->push([
                    'no' => $no++,
                    'nama' => mb_convert_encoding($sasaran->nama_sasaran ?? '', 'UTF-8', 'UTF-8'),
                    'nik' => $sasaran->nik_sasaran ?? '',
                    'kategori' => 'Lansia',
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
                    'umur' => $sasaran->umur_sasaran ? $sasaran->umur_sasaran . ' tahun' : '-',
                    'jenis_kelamin' => mb_convert_encoding($sasaran->jenis_kelamin ?? '-', 'UTF-8', 'UTF-8'),
                    'alamat' => mb_convert_encoding($sasaran->alamat_sasaran ?? '-', 'UTF-8', 'UTF-8'),
                ]);
            }
        }

        $filename = 'Data_Keluarga_' . date('Y-m-d_His') . '.pdf';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);

        $userName = mb_convert_encoding($user->name ?? '', 'UTF-8', 'UTF-8');

        $html = view('pdf.data-keluarga', [
            'allKeluarga' => $allKeluarga,
            'user' => (object) ['name' => $userName],
            'noKk' => $noKk,
            'generatedAt' => now('Asia/Jakarta'),
        ])->render();

        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
