#!/bin/bash

# Script untuk setup Laravel di Hostinger
# 1. Jalankan php artisan storage:link
# 2. Pindahkan isi folder public ke public_html

echo "=== Setup Laravel di Hostinger ==="
echo ""

# Pastikan di direktori root project
if [ ! -f "artisan" ]; then
    echo "❌ Error: File artisan tidak ditemukan. Pastikan Anda di direktori root project."
    exit 1
fi

# 1. Jalankan storage:link
echo "1. Menjalankan php artisan storage:link..."
php artisan storage:link

if [ $? -eq 0 ]; then
    echo "✅ storage:link berhasil"
else
    echo "⚠️  storage:link gagal (mungkin exec() disabled, tidak masalah)"
fi

echo ""

# 2. Pindahkan isi folder public ke public_html
echo "2. Memindahkan isi folder public ke public_html..."

# Pastikan public_html ada
if [ ! -d "public_html" ]; then
    echo "Membuat folder public_html..."
    mkdir -p public_html
fi

# Copy semua file dari public ke public_html (preserve symlinks)
echo "Menyalin file dari public/ ke public_html/..."
rsync -av --links public/ public_html/

# Atau jika rsync tidak tersedia, gunakan cp
if [ $? -ne 0 ]; then
    echo "Menggunakan cp sebagai alternatif..."
    cp -r -L public/* public_html/ 2>/dev/null || cp -r public/* public_html/
fi

echo ""
echo "✅ Selesai!"
echo ""
echo "Verifikasi:"
echo "- Cek symlink: ls -la public_html/storage"
echo "- Cek file: ls -la public_html/"
echo ""
echo "Catatan:"
echo "- File di public_html/ sekarang adalah copy dari public/"
echo "- Symlink storage sudah dibuat di public/storage"
echo "- Jika perlu, buat juga symlink di public_html/storage:"
echo "  ln -s ../storage/app/public public_html/storage"
