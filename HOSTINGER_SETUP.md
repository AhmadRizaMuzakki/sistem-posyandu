# Setup Hostinger – Sistem Posyandu (Laravel 12)

Panduan singkat agar tampilan dan aplikasi berjalan **aman dan lancar** di Hostinger, sama seperti di local.

---

## 1. Document root (wajib)

Set **Document Root** ke folder `public`:

- Hostinger → **Domains** → posyandukaranggan.com → **Manage** → **Domain Configuration**
- **Document Root**: ubah ke  
  `public_html/public`  
  (bukan sekadar `public_html`)

Struktur deploy: file Laravel ada di `public_html/` (app, bootstrap, config, **public**, storage, vendor, …). Web server harus menjalankan `public/index.php`, maka root harus `public_html/public`.

---

## 2. File `.env` di server

`.env` **tidak** di-deploy. Buat manual di **root project** (satu tingkat di atas `public`),即 di `public_html/.env`.

1. Salin isi `env.hostinger.example` ke `.env`.
2. Sesuaikan:
   - `APP_URL=https://posyandukaranggan.com` (pakai HTTPS dan domain Anda)
   - `APP_DEBUG=false`
   - `APP_KEY=` → generate: jalankan `php artisan key:generate` di server (SSH/Terminal Hostinger) atau isi key dari lokal
   - `DB_*` → sesuai database MySQL Hostinger

---

## 3. Hak akses folder

Pastikan folder berikut **writable** (755 atau 775):

- `storage`
- `storage/framework`
- `storage/framework/cache`
- `storage/framework/sessions`
- `storage/logs`
- `bootstrap/cache`

Di File Manager Hostinger: klik kanan folder → **Permissions** → atur sesuai kebutuhan.

---

## 4. Symlink `storage` (jika pakai fitur upload)

Jika ada upload file (logo, SK, dll.) yang disimpan di `storage/app/public`:

```bash
php artisan storage:link
```

Jalankan di root project via SSH/Terminal Hostinger.

---

## 5. Setelah deploy

1. Pastikan **Document Root** = `public_html/public`.
2. Ada `.env` di root (`public_html/`), nilai `APP_URL` dan `APP_DEBUG` sudah benar.
3. `storage` dan `bootstrap/cache` writable.
4. Buka `https://posyandukaranggan.com` → halaman login dan tampilan lain harus sama lancar seperti di local.

---

## Ringkasan checklist

| Langkah | Status |
|--------|--------|
| Document root = `public_html/public` | ☐ |
| Buat `.env` dari `env.hostinger.example` | ☐ |
| `APP_URL` HTTPS, `APP_DEBUG=false` | ☐ |
| `APP_KEY` diisi / `php artisan key:generate` | ☐ |
| `DB_*` sesuai database Hostinger | ☐ |
| `storage` & `bootstrap/cache` writable | ☐ |
| `php artisan storage:link` (jika pakai upload) | ☐ |

---

Deploy via GitHub Actions sudah mengirim **vendor** dan **build assets**. Setelah checklist di atas selesai, aplikasi siap dipakai di Hostinger dengan aman dan lancar.
