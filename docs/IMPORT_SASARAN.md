# Panduan Import Data Sasaran

Import disesuaikan dengan **inputan form** setiap jenis sasaran. Gunakan template CSV dari tombol **"Unduh contoh CSV"** di modal Import masing-masing kategori, atau ikuti kolom di bawah ini.

## Aturan Umum

- **Baris pertama** = header (nama kolom), jangan dihapus.
- **Format tanggal:** `YYYY-MM-DD` (contoh: 2022-05-15) atau `DD/MM/YYYY`.
- **Jenis kelamin:** harus persis `Laki-laki` atau `Perempuan`.
- **Status keluarga:** `kepala keluarga`, `istri`, atau `anak`. Kolom boleh bernama `status_kel` (alias `status_keluarga`).
- **Kepersertaan BPJS:** `PBI` atau `NON PBI`.
- **NIK dan No KK:** angka saja (16 digit). Baris dengan NIK kosong akan gagal.
- Jika **NIK + Posyandu** sudah ada di database, baris akan **dilewati** (tidak duplikat).

---

## 1. Bayi/Balita

**Kolom (urutan bebas, nama harus sama):**

| Kolom | Wajib | Keterangan |
|-------|-------|------------|
| nik_sasaran | Ya | NIK bayi/balita (angka) |
| nama_sasaran | Ya | Nama lengkap |
| no_kk_sasaran | Ya | No KK (angka) |
| tempat_lahir | - | Kota/kabupaten |
| tanggal_lahir | Ya | YYYY-MM-DD |
| jenis_kelamin | Ya | Laki-laki / Perempuan |
| status_keluarga | - | anak / kepala keluarga / istri |
| alamat_sasaran | Ya | Alamat lengkap |
| rt, rw | - | RT, RW |
| kepersertaan_bpjs | - | PBI / NON PBI |
| nomor_bpjs | - | Nomor BPJS |
| nik_orangtua | Ya | NIK orang tua |
| nama_orangtua | Ya | Nama orang tua |
| tempat_lahir_orangtua | - | Tempat lahir orang tua |
| tanggal_lahir_orangtua | - | YYYY-MM-DD |
| pekerjaan_orangtua | - | Pekerjaan (mis. Ibu Rumah Tangga, Wiraswasta) |

**Contoh baris (header + 1 data):**

```csv
nik_sasaran,nama_sasaran,no_kk_sasaran,tempat_lahir,tanggal_lahir,jenis_kelamin,status_keluarga,alamat_sasaran,rt,rw,kepersertaan_bpjs,nomor_bpjs,nik_orangtua,nama_orangtua,tempat_lahir_orangtua,tanggal_lahir_orangtua,pekerjaan_orangtua
3201234567890001,Ahmad Budi,3201234567890002,Jakarta,2022-05-15,Laki-laki,anak,Jl. Merdeka No 10,01,02,NON PBI,0001234567890,3201234567890003,Siti Aminah,Bandung,1990-03-20,Ibu Rumah Tangga
```

---

## 2. Remaja

**Kolom:** nik_sasaran, nama_sasaran, no_kk_sasaran, tempat_lahir, tanggal_lahir, jenis_kelamin, status_keluarga, alamat_sasaran, rt, rw, kepersertaan_bpjs, nomor_bpjs, nomor_telepon, pendidikan, nik_orangtua, nama_orangtua.

**Pendidikan** (opsional): contoh `SLTP/Sederajat`, `SLTA/Sederajat`, `Tamat SD/Sederajat`, dll.

---

## 3. Dewasa

**Kolom:** nik_sasaran, nama_sasaran, no_kk_sasaran, tempat_lahir, tanggal_lahir, jenis_kelamin, status_keluarga, alamat_sasaran, rt, rw, kepersertaan_bpjs, nomor_bpjs, nomor_telepon, pekerjaan, pendidikan.

**Pekerjaan:** bebas teks (contoh: Karyawan Swasta, Mengurus Rumah Tangga, Wiraswasta).

---

## 4. Ibu Hamil

**Kolom:** nik_sasaran, nama_sasaran, no_kk_sasaran, tempat_lahir, tanggal_lahir, jenis_kelamin, status_keluarga, alamat_sasaran, rt, rw, kepersertaan_bpjs, nomor_bpjs, nomor_telepon, pekerjaan, pendidikan, **minggu_kandungan**, **nama_suami**, **nik_suami**, **pekerjaan_suami**, **status_keluarga_suami**.

**minggu_kandungan:** angka (contoh: 20). **status_keluarga_suami:** `kepala keluarga` atau `istri`.

---

## 5. Pralansia & 6. Lansia

**Kolom:** sama dengan Dewasa — nik_sasaran, nama_sasaran, no_kk_sasaran, tempat_lahir, tanggal_lahir, jenis_kelamin, status_keluarga, alamat_sasaran, rt, rw, kepersertaan_bpjs, nomor_bpjs, nomor_telepon, pekerjaan, pendidikan.

---

## Format File

- **CSV:** Simpan dengan encoding **UTF-8**. Di Excel: Simpan Sebagai → CSV UTF-8.
- **Excel:** .xlsx/.xls didukung jika PhpSpreadsheet terpasang di server.
- **Tanggal Excel:** Format `DD/MM/YYYY` (contoh: 08/01/1998) dan serial date Excel otomatis didukung.
- **NIK/No KK di Excel:** Format kolom sebagai **Teks** agar angka panjang tidak terpotong (klik kanan kolom → Format Sel → Teks).

Agar import tidak gagal, pastikan:

1. NIK dan nama tidak kosong.
2. Tanggal lahir valid (format di atas).
3. Jenis kelamin persis **Laki-laki** atau **Perempuan**.
4. Untuk Bayi/Balita dan Remaja, isi **nik_orangtua** dan **nama_orangtua**.
