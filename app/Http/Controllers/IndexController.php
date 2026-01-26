<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\JadwalKegiatan;
use App\Models\SasaranBayibalita;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\Orangtua;
use App\Models\Kader;
use App\Models\Imunisasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        // Ambil posyandu pertama sebagai default, atau bisa dikonfigurasi
        // Jika ada parameter posyandu di query string, gunakan itu
        $posyanduId = $request->query('posyandu');
        $filterPosyandu = $request->query('filter_posyandu');
        $search = $request->query('search');
        
        if ($posyanduId) {
            $posyandu = Posyandu::find($posyanduId);
        } else {
            // Ambil posyandu pertama sebagai default
            $posyandu = Posyandu::first();
        }

        // Ambil semua posyandu untuk filter dropdown dan daftar posyandu
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu', 'alamat_posyandu', 'domisili_posyandu')
            ->orderBy('nama_posyandu')
            ->get();

        // Ambil acara dari SEMUA posyandu untuk ditampilkan di halaman depan
        $today = Carbon::now()->startOfDay();
        $startDate = Carbon::now()->subDays(30)->startOfDay(); // 30 hari terakhir untuk acara baru
        $endDate = Carbon::now()->addMonths(2)->endOfMonth();
        
        // Query: tampilkan semua acara dari semua posyandu yang:
        // 1. Tanggalnya >= hari ini sampai 2 bulan ke depan, ATAU
        // 2. Baru dibuat dalam 30 hari terakhir (untuk menampilkan acara baru meskipun tanggalnya jauh)
        $query = JadwalKegiatan::with('posyandu')
            ->where(function($q) use ($today, $endDate, $startDate) {
                $q->where(function($subQ) use ($today, $endDate) {
                    // Acara dengan tanggal >= hari ini sampai 2 bulan ke depan
                    $subQ->where('tanggal', '>=', $today->format('Y-m-d'))
                      ->where('tanggal', '<=', $endDate->format('Y-m-d'));
                })->orWhere(function($subQ) use ($startDate) {
                    // Acara yang baru dibuat dalam 30 hari terakhir
                    $subQ->where('created_at', '>=', $startDate);
                });
            });

        // Filter berdasarkan posyandu jika dipilih
        if ($filterPosyandu) {
            $query->where('id_posyandu', $filterPosyandu);
        }

        // Search berdasarkan nama acara atau tempat
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', '%' . $search . '%')
                  ->orWhere('tempat', 'like', '%' . $search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }

        // Pagination: 12 acara per halaman
        $acaraList = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(12)
            ->withQueryString(); // Mempertahankan query string untuk filter
        
        // Hitung statistik gabungan dari semua posyandu (12 posyandu)
        $totalBalita = SasaranBayibalita::count();
        $totalRemaja = SasaranRemaja::count();
        $totalOrangtua = SasaranDewasa::count(); // Dewasa = Orangtua
        $totalIbuHamil = SasaranIbuhamil::count();
        $totalPralansia = SasaranPralansia::count();
        $totalLansia = SasaranLansia::count();
        $totalKader = Kader::count();
        
        // Hitung cakupan imunisasi (persentase dari total bayi/balita yang sudah diimunisasi)
        // Cakupan = (jumlah bayi/balita yang sudah diimunisasi / total bayi/balita) * 100
        $totalBayiBalitaTerimunisasi = DB::table('imunisasi')
            ->where('kategori_sasaran', 'bayibalita')
            ->whereNotNull('id_sasaran')
            ->distinct('id_sasaran')
            ->count('id_sasaran');
        
        $cakupanImunisasi = $totalBalita > 0 
            ? round(($totalBayiBalitaTerimunisasi / $totalBalita) * 100) 
            : 0;
        // Batasi maksimal 100%
        $cakupanImunisasi = min($cakupanImunisasi, 100);
        
        if (!$posyandu) {
            // Jika tidak ada posyandu, tetap tampilkan halaman dengan data kosong
            $posyandu = null;
        }

        return view('index', [
            'posyandu' => $posyandu,
            'acaraList' => $acaraList,
            'daftarPosyandu' => $daftarPosyandu,
            'filterPosyandu' => $filterPosyandu,
            'search' => $search,
            'totalBalita' => $totalBalita,
            'totalRemaja' => $totalRemaja,
            'totalOrangtua' => $totalOrangtua,
            'totalIbuHamil' => $totalIbuHamil,
            'totalPralansia' => $totalPralansia,
            'totalLansia' => $totalLansia,
            'totalKader' => $totalKader,
            'cakupanImunisasi' => $cakupanImunisasi,
        ]);
    }
}
