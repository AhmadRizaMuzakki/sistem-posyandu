<?php

namespace App\Livewire\Orangtua;

use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class OrangtuaDashboard extends Component
{
    #[Layout('layouts.orangtuadashboard')]
    public function render()
    {
        $user = Auth::user();

        // Ekstrak No KK dari email user (email format: no_kk + '@gmail.com')
        $noKk = null;
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            $noKk = str_replace('@gmail.com', '', $user->email);
        }

        // Cari orangtua berdasarkan No KK (bukan berdasarkan nama, karena nama bisa berubah)
        $orangtua = null;
        if ($noKk) {
            $orangtua = Orangtua::where('no_kk', $noKk)->first();
        }

        $allKeluarga = collect();

        // Ambil semua sasaran berdasarkan No KK yang diekstrak dari email user
        // Tidak perlu menunggu ada orangtua, karena sasaran bisa langsung menggunakan No KK
        if ($noKk) {
            // Ambil semua sasaran Bayi/Balita, Remaja, Dewasa, Pralansia, dan Lansia berdasarkan no_kk_sasaran yang sama dengan No KK user
            $sasaranBayi = SasaranBayibalita::where('no_kk_sasaran', $noKk)->get();
            $sasaranRemaja = SasaranRemaja::where('no_kk_sasaran', $noKk)->get();
            $sasaranDewasa = SasaranDewasa::where('no_kk_sasaran', $noKk)->get();
            $sasaranPralansia = SasaranPralansia::where('no_kk_sasaran', $noKk)->get();
            $sasaranLansia = SasaranLansia::where('no_kk_sasaran', $noKk)->get();

            // Kumpulkan semua sasaran dengan informasi lengkap
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

        // Ekstrak No KK dari email user
        $noKk = null;
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            $noKk = str_replace('@gmail.com', '', $user->email);
        }

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
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
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
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
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
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
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
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
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
                    'tanggal_lahir' => $sasaran->tanggal_lahir ? \Carbon\Carbon::parse($sasaran->tanggal_lahir)->format('d/m/Y') : '-',
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

        // Sanitize user name untuk menghindari karakter tidak valid
        $userName = mb_convert_encoding($user->name ?? '', 'UTF-8', 'UTF-8');

        $html = view('pdf.data-keluarga', [
            'allKeluarga' => $allKeluarga,
            'user' => (object)['name' => $userName],
            'noKk' => $noKk,
            'generatedAt' => now('Asia/Jakarta'),
        ])->render();

        // Pastikan HTML adalah UTF-8 yang valid
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Return sebagai stream response untuk Livewire
        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
