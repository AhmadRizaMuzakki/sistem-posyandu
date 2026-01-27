# Setup Hostinger – Sistem Posyandu (Laravel 12)

Panduan singkat agar tampilan dan aplikasi berjalan **aman dan lancar** di Hostinger, sama seperti di local.

---

## 1. Root `.htaccess` (mengatasi 403 Forbidden)

Di **root project** (folder `public_html/` di Hostinger) sudah ada file **`.htaccess`** yang meneruskan semua request ke folder `public/`. File ini ikut di-deploy.

- **Document root** tetap `public_html` (tidak perlu diubah ke `public_html/public`).
- Semua akses (/, /login, /Login_v1/..., dll.) otomatis diarahkan ke `public/`.

Jika muncul **403 Forbidden**:

1. **Cek permission**
   - Folder: `755` (atau `775` jika perlu)
   - File: `644`
   - Pastikan `public`, `storage`, `bootstrap/cache` bisa dibaca web server.

2. **Pastikan mod_rewrite aktif**
   - Hostinger biasanya sudah aktif. Jika ragu, hubungi dukungan.

3. **Hapus `.htaccess` lain** di `public_html/` yang memblokir akses (mis. `Deny from all`).

---

## 2. File `.env` di server

`.env` **tidak** di-deploy. Buat manual di **root project** (`public_html/.env`).

1. Salin isi `env.hostinger.example` ke `.env`.
2. Sesuaikan:
   - `APP_URL=https://posyandukaranggan.com` (HTTPS + domain Anda)
   - `APP_DEBUG=false`
   - `APP_KEY=` → jalankan `php artisan key:generate` di server, atau salin dari lokal
   - `DB_*` → sesuai database MySQL Hostinger

---

## 3. `vendor` dan Composer

**`vendor`** tidak ikut deploy (di-exclude). Wajib jalankan Composer di server:

```bash
composer install --no-dev --optimize-autoloader
```

Jalankan di **root project** via SSH atau **Terminal** di File Manager Hostinger.

---

## 4. Hak akses folder

Pastikan folder berikut **writable** (755 atau 775):

- `storage`, `storage/framework`, `storage/framework/cache`, `storage/framework/sessions`, `storage/logs`
- `bootstrap/cache`

Di File Manager: klik kanan folder → **Permissions**.

---

## 5. Storage di `public_html` (logo, SK)

Deploy mengikuti langkah: **jalankan `php artisan storage:link` dulu**, lalu pindah **isi** folder `public` ke `public_html`. Folder Laravel **tidak** disimpan di `public_html`.

- Workflow deploy sudah menjalankan `storage:link` sebelum copy `public` → `public_html`.
- Symlink `public_html/storage` → `../posyandukaranggan/storage/app/public` ikut disiapkan di deploy.
- FTP kadang tidak preserve symlink. Jika `/storage/...` tidak jalan, buat manual via **SSH**:
  ```bash
  cd /domains/posyandukaranggan.com/public_html
  ln -s ../posyandukaranggan/storage/app/public storage
  ```
- Jika `symlink()` disabled di Hostinger, route `/storage/{path}` (PHP) tetap melayani file dari `storage/app/public` sebagai alternatif.
- Pastikan `storage` dan `storage/app/public` **writable** (755).

---

## Ringkasan checklist

| Langkah | Status |
|--------|--------|
| Root `.htaccess` ada (ikut deploy) | ☐ |
| Document root = `public_html` | ☐ |
| Buat `.env` dari `env.hostinger.example` | ☐ |
| `APP_URL` HTTPS, `APP_DEBUG=false` | ☐ |
| `APP_KEY` ada; `DB_*` sesuai Hostinger | ☐ |
| `composer install` di server | ☐ |
| `storage` & `bootstrap/cache` writable | ☐ |
| Symlink `public_html/storage` → `../posyandukaranggan/...` (atau gunakan route) | ☐ |

---

Setelah checklist selesai, buka `https://posyandukaranggan.com`. Halaman login dan tampilan lain harus lancar seperti di local.
