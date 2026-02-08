<?php

namespace App\Livewire\Posyandu;

use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Models\Jadwal;
use App\Models\JadwalKegiatan;
use App\Models\PetugasKesehatan;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PosyanduJadwal extends Component
{
    use PosyanduHelper;

    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $isModalOpen = false;
    public $isDetailModalOpen = false;
    public $detailDate = null;
    public $jadwalId = null;
    public $id_petugas_kesehatan = null;
    public $keterangan = '';
    public $tanggal = '';
    public $presensi = 'belum_hadir';
    public $activeTab = 'jadwal';
    public $absenDate = null;

    // Kegiatan/acara (card terpisah)
    public $tanggal_acara = '';
    public $nama_kegiatan = '';
    public $tempat = '';
    public $deskripsi_kegiatan = '';
    public $jam_mulai = '';
    public $jam_selesai = '';
    public $id_jadwal_kegiatan_edit = null;

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
        $this->absenDate = date('Y-m-d');
        $this->tanggal_acara = date('Y-m-d');
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToToday()
    {
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
    }

    public function openModal($date = null)
    {
        if ($date) {
            $this->selectedDate = $date;
            $this->tanggal = $date;
            $this->tanggal_acara = $date; // Update tanggal di form acara juga
        } else {
            $this->tanggal = $this->selectedDate ?? date('Y-m-d');
        }
        $this->jadwalId = null;
        $this->id_petugas_kesehatan = null;
        $this->keterangan = '';
        $this->presensi = 'belum_hadir';
        $this->isModalOpen = true;
    }

    /** Update tanggal di form acara dan petugas ketika klik tanggal di kalender */
    public function selectDate($date)
    {
        $this->tanggal_acara = $date;
        $this->tanggal = $date;
    }

    public function openAddJadwalModal()
    {
        // Tutup modal detail jika terbuka
        if ($this->isDetailModalOpen) {
            $this->closeDetailModal();
        }
        // Tutup modal lama jika terbuka
        if ($this->isModalOpen) {
            $this->closeModal();
        }
        // Gunakan tanggal dari detailDate jika ada, atau tanggal hari ini
        $date = $this->detailDate ?? date('Y-m-d');
        $this->selectedDate = $date;
        $this->tanggal = $date;
        $this->openModal($this->tanggal);
    }

    public function openEditModal($jadwalId)
    {
        $jadwal = Jadwal::findOrFail($jadwalId);
        if ($jadwal->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        
        $this->selectedDate = $jadwal->tanggal->format('Y-m-d');
        $this->tanggal = $jadwal->tanggal->format('Y-m-d');
        $this->jadwalId = $jadwal->id_jadwal;
        $this->id_petugas_kesehatan = $jadwal->id_petugas_kesehatan;
        $this->keterangan = $jadwal->keterangan ?? '';
        $this->presensi = $jadwal->presensi ?? 'belum_hadir';
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->selectedDate = null;
        $this->jadwalId = null;
        $this->id_petugas_kesehatan = null;
        $this->keterangan = '';
        $this->tanggal = '';
        $this->presensi = 'belum_hadir';
    }

    public function editKegiatan($id)
    {
        $k = JadwalKegiatan::findOrFail($id);
        if ($k->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        $this->id_jadwal_kegiatan_edit = $k->id_jadwal_kegiatan;
        $this->tanggal_acara = $k->tanggal->format('Y-m-d');
        $this->nama_kegiatan = $k->nama_kegiatan ?? '';
        $this->tempat = $k->tempat ?? '';
        $this->deskripsi_kegiatan = $k->deskripsi ?? '';
        $this->jam_mulai = $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '';
        $this->jam_selesai = $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '';
        $this->activeTab = 'jadwal';
    }

    public function cancelEditKegiatan()
    {
        $this->id_jadwal_kegiatan_edit = null;
        $this->nama_kegiatan = '';
        $this->tempat = '';
        $this->deskripsi_kegiatan = '';
        $this->jam_mulai = '';
        $this->jam_selesai = '';
    }

    public function saveKegiatan()
    {
        $this->validate([
            'tanggal_acara' => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'tempat' => 'nullable|string|max:255',
            'deskripsi_kegiatan' => 'nullable|string',
            'jam_mulai' => 'nullable|string|regex:/^\d{1,2}:\d{2}(:\d{2})?$/',
            'jam_selesai' => 'nullable|string|regex:/^\d{1,2}:\d{2}(:\d{2})?$/',
        ], [
            'tanggal_acara.required' => 'Tanggal wajib diisi.',
            'nama_kegiatan.required' => 'Nama acara wajib diisi.',
        ]);

        $jamMulai = $this->normalizeTime($this->jam_mulai);
        $jamSelesai = $this->normalizeTime($this->jam_selesai);

        if ($this->id_jadwal_kegiatan_edit) {
            $k = JadwalKegiatan::findOrFail($this->id_jadwal_kegiatan_edit);
            if ($k->id_posyandu != $this->posyanduId) {
                abort(403, 'Unauthorized access');
            }
            $k->update([
                'tanggal' => $this->tanggal_acara,
                'nama_kegiatan' => trim($this->nama_kegiatan),
                'tempat' => $this->tempat ? trim($this->tempat) : null,
                'deskripsi' => $this->deskripsi_kegiatan ? trim($this->deskripsi_kegiatan) : null,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
            ]);
            session()->flash('message', 'Acara berhasil diperbarui.');
        } else {
            JadwalKegiatan::create([
                'id_posyandu' => $this->posyanduId,
                'tanggal' => $this->tanggal_acara,
                'nama_kegiatan' => trim($this->nama_kegiatan),
                'tempat' => $this->tempat ? trim($this->tempat) : null,
                'deskripsi' => $this->deskripsi_kegiatan ? trim($this->deskripsi_kegiatan) : null,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
            ]);
            session()->flash('message', 'Acara berhasil disimpan.');
        }

        $this->id_jadwal_kegiatan_edit = null;
        $this->nama_kegiatan = '';
        $this->tempat = '';
        $this->deskripsi_kegiatan = '';
        $this->jam_mulai = '';
        $this->jam_selesai = '';
        session()->flash('messageType', 'success');
    }

    /** Normalisasi jam ke format H:i:s untuk kolom TIME. */
    private function normalizeTime(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $parts = explode(':', $value);
        if (count($parts) === 2) {
            return sprintf('%02d:%02d:00', (int) $parts[0], (int) $parts[1]);
        }
        if (count($parts) === 3) {
            return sprintf('%02d:%02d:%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
        }
        return null;
    }

    public function deleteKegiatan($id)
    {
        $k = JadwalKegiatan::findOrFail($id);
        if ($k->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        $k->delete();
        session()->flash('message', 'Kegiatan berhasil dihapus.');
        session()->flash('messageType', 'success');
    }

    public function saveJadwal()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'id_petugas_kesehatan' => 'required|exists:petugas_kesehatan,id_petugas_kesehatan',
            'keterangan' => 'nullable|string',
            'presensi' => 'nullable|in:hadir,tidak_hadir,belum_hadir',
        ]);

        // Validasi petugas kesehatan milik posyandu yang sama
        $petugas = PetugasKesehatan::findOrFail($this->id_petugas_kesehatan);
        if ($petugas->id_posyandu != $this->posyanduId) {
            session()->flash('message', 'Petugas kesehatan tidak terdaftar di posyandu ini.');
            session()->flash('messageType', 'error');
            return;
        }

        // Default presensi jika tidak diisi (dari card tidak ada presensi)
        $presensi = $this->presensi ? strtolower(trim($this->presensi)) : 'belum_hadir';
        
        // Validasi nilai presensi
        if (!in_array($presensi, ['hadir', 'tidak_hadir', 'belum_hadir'])) {
            $presensi = 'belum_hadir';
        }

        if ($this->jadwalId) {
            // Update existing
            $jadwal = Jadwal::findOrFail($this->jadwalId);
            if ($jadwal->id_posyandu != $this->posyanduId) {
                abort(403, 'Unauthorized access');
            }
            $jadwal->update([
                'tanggal' => $this->tanggal,
                'id_petugas_kesehatan' => $this->id_petugas_kesehatan,
                'keterangan' => $this->keterangan ?: null,
                'presensi' => $presensi,
            ]);
            session()->flash('message', 'Jadwal berhasil diperbarui.');
        } else {
            // Create new - bisa multiple petugas di tanggal yang sama
            DB::table('jadwals')->insert([
                'id_posyandu' => $this->posyanduId,
                'tanggal' => $this->tanggal,
                'id_petugas_kesehatan' => $this->id_petugas_kesehatan,
                'keterangan' => $this->keterangan ?: null,
                'presensi' => $presensi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            session()->flash('message', 'Petugas berhasil ditambahkan.');
        }

        session()->flash('messageType', 'success');
        $this->jadwalId = null;
        $this->id_petugas_kesehatan = null;
        $this->keterangan = '';
        $this->presensi = 'belum_hadir';
    }

    public function deleteJadwal($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        if ($jadwal->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        $jadwal->delete();
        session()->flash('message', 'Jadwal berhasil dihapus.');
        session()->flash('messageType', 'success');
    }

    public function getJadwalForDate($date)
    {
        return Jadwal::where('id_posyandu', $this->posyanduId)
            ->where('tanggal', $date)
            ->first();
    }

    public function updatePresensi($id, $presensi)
    {
        $jadwal = Jadwal::findOrFail($id);
        if ($jadwal->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        // Pastikan presensi dalam lowercase dan valid
        $presensi = strtolower(trim($presensi));
        
        if (!in_array($presensi, ['hadir', 'tidak_hadir', 'belum_hadir'])) {
            session()->flash('message', 'Nilai presensi tidak valid.');
            session()->flash('messageType', 'error');
            return;
        }
        
        $jadwal->update([
            'presensi' => $presensi,
        ]);
        session()->flash('message', 'Presensi berhasil diperbarui.');
        session()->flash('messageType', 'success');
    }

    public function render()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        // Calculate the date range for the calendar view (including previous and next month days)
        $firstDayOfWeek = $startOfMonth->dayOfWeek;
        $prevMonthStart = $startOfMonth->copy()->subMonth()->startOfMonth();
        $daysInPrevMonth = $prevMonthStart->daysInMonth;
        $calendarStartDate = $prevMonthStart->copy()->addDays($daysInPrevMonth - $firstDayOfWeek);
        $calendarEndDate = $endOfMonth->copy()->addDays(42 - ($firstDayOfWeek + $startOfMonth->daysInMonth));
        
        $jadwals = Jadwal::where('id_posyandu', $this->posyanduId)
            ->whereBetween('tanggal', [$calendarStartDate->format('Y-m-d'), $calendarEndDate->format('Y-m-d')])
            ->with('petugasKesehatan')
            ->get()
            ->groupBy(fn ($j) => $j->tanggal->format('Y-m-d'));

        $kegiatans = JadwalKegiatan::where('id_posyandu', $this->posyanduId)
            ->whereBetween('tanggal', [$calendarStartDate->format('Y-m-d'), $calendarEndDate->format('Y-m-d')])
            ->get()
            ->groupBy(fn ($k) => $k->tanggal->format('Y-m-d'));

        $petugasKesehatanList = PetugasKesehatan::where('id_posyandu', $this->posyanduId)
            ->orderBy('nama_petugas_kesehatan')
            ->get();

        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek;
        $calendarDays = [];
        $prevMonth = $startOfMonth->copy()->subMonth();
        $daysInPrevMonth = $prevMonth->daysInMonth;

        for ($i = $firstDayOfWeek - 1; $i >= 0; $i--) {
            $day = $daysInPrevMonth - $i;
            $date = Carbon::create($prevMonth->year, $prevMonth->month, $day);
            $dateKey = $date->format('Y-m-d');
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateKey,
                'isToday' => $date->isToday(),
                'isOtherMonth' => true,
                'jadwals' => $jadwals->get($dateKey, collect()),
                'kegiatans' => $kegiatans->get($dateKey, collect()),
            ];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
            $dateKey = $date->format('Y-m-d');
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateKey,
                'isToday' => $date->isToday(),
                'isOtherMonth' => false,
                'jadwals' => $jadwals->get($dateKey, collect()),
                'kegiatans' => $kegiatans->get($dateKey, collect()),
            ];
        }

        $totalCells = count($calendarDays);
        $remainingCells = 42 - $totalCells;
        if ($remainingCells > 0) {
            $nextMonth = $endOfMonth->copy()->addDay();
            for ($day = 1; $day <= $remainingCells; $day++) {
                $date = Carbon::create($nextMonth->year, $nextMonth->month, $day);
                $dateKey = $date->format('Y-m-d');
                $calendarDays[] = [
                    'day' => $day,
                    'date' => $dateKey,
                    'isToday' => $date->isToday(),
                    'isOtherMonth' => true,
                    'jadwals' => $jadwals->get($dateKey, collect()),
                    'kegiatans' => $kegiatans->get($dateKey, collect()),
                ];
            }
        }

        $monthName = $startOfMonth->locale('id')->translatedFormat('F Y');

        $selectedDateJadwals = collect();
        if ($this->selectedDate) {
            $selectedDateJadwals = Jadwal::where('id_posyandu', $this->posyanduId)
                ->where('tanggal', $this->selectedDate)
                ->with('petugasKesehatan')
                ->get();
        }

        // Query terpisah untuk pagination list acara (hanya bulan ini)
        $kegiatansList = JadwalKegiatan::where('id_posyandu', $this->posyanduId)
            ->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(10);

        $absenJadwals = collect();
        if ($this->absenDate) {
            $absenJadwals = Jadwal::where('id_posyandu', $this->posyanduId)
                ->whereDate('tanggal', $this->absenDate)
                ->with('petugasKesehatan')
                ->orderBy('created_at')
                ->get();
        }

        // Data untuk modal detail hari
        $detailDateJadwals = collect();
        $detailDateKegiatans = collect();
        if ($this->detailDate) {
            $detailDateJadwals = Jadwal::where('id_posyandu', $this->posyanduId)
                ->where('tanggal', $this->detailDate)
                ->with('petugasKesehatan')
                ->orderBy('created_at')
                ->get();
            
            $detailDateKegiatans = JadwalKegiatan::where('id_posyandu', $this->posyanduId)
                ->where('tanggal', $this->detailDate)
                ->orderBy('jam_mulai')
                ->get();
        }

        return view('livewire.posyandu.posyandu-jadwal', [
            'title' => 'Jadwal - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'calendarDays' => $calendarDays,
            'monthName' => $monthName,
            'petugasKesehatanList' => $petugasKesehatanList,
            'selectedDateJadwals' => $selectedDateJadwals,
            'kegiatansList' => $kegiatansList,
            'absenJadwals' => $absenJadwals,
            'detailDateJadwals' => $detailDateJadwals,
            'detailDateKegiatans' => $detailDateKegiatans,
        ]);
    }
}
