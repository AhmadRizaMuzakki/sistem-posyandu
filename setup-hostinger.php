<?php

/**
 * Script untuk setup Laravel di Hostinger
 * 1. Jalankan php artisan storage:link
 * 2. Pindahkan isi folder public ke public_html
 * 
 * Jalankan: php setup-hostinger.php
 */

echo "=== Setup Laravel di Hostinger ===\n\n";

// Pastikan di direktori root project
if (!file_exists('artisan')) {
    echo "❌ Error: File artisan tidak ditemukan. Pastikan Anda di direktori root project.\n";
    exit(1);
}

// 1. Jalankan storage:link
echo "1. Menjalankan php artisan storage:link...\n";
$output = [];
$return_var = 0;
exec('php artisan storage:link 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "✅ storage:link berhasil\n";
    foreach ($output as $line) {
        echo "   {$line}\n";
    }
} else {
    echo "⚠️  storage:link gagal (mungkin exec() disabled, tidak masalah)\n";
    foreach ($output as $line) {
        echo "   {$line}\n";
    }
}

echo "\n";

// 2. Pindahkan isi folder public ke public_html
echo "2. Memindahkan isi folder public ke public_html...\n";

// Pastikan public_html ada
if (!is_dir('public_html')) {
    echo "   Membuat folder public_html...\n";
    mkdir('public_html', 0755, true);
}

// Pastikan folder public ada
if (!is_dir('public')) {
    echo "❌ Error: Folder public tidak ditemukan.\n";
    exit(1);
}

// Copy semua file dari public ke public_html
echo "   Menyalin file dari public/ ke public_html/...\n";

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
    }
    
    closedir($dir);
}

copyDirectory('public', 'public_html');

echo "   ✅ File berhasil disalin\n";

// 3. Buat symlink storage di public_html jika belum ada
echo "\n3. Membuat symlink storage di public_html...\n";
$publicHtmlStorage = 'public_html/storage';
$storageTarget = '../storage/app/public';

if (!is_link($publicHtmlStorage) && !is_dir($publicHtmlStorage)) {
    if (function_exists('symlink')) {
        if (symlink($storageTarget, $publicHtmlStorage)) {
            echo "   ✅ Symlink berhasil dibuat: {$publicHtmlStorage} -> {$storageTarget}\n";
        } else {
            echo "   ⚠️  Gagal membuat symlink (mungkin permission issue)\n";
            echo "   Jalankan manual: ln -s {$storageTarget} {$publicHtmlStorage}\n";
        }
    } else {
        echo "   ⚠️  Fungsi symlink() tidak tersedia\n";
        echo "   Aplikasi akan menggunakan route /storage/{path} sebagai alternatif\n";
    }
} else {
    if (is_link($publicHtmlStorage)) {
        $linkTarget = readlink($publicHtmlStorage);
        echo "   ✅ Symlink sudah ada: {$publicHtmlStorage} -> {$linkTarget}\n";
    } else {
        echo "   ✅ Directory {$publicHtmlStorage} sudah ada\n";
    }
}

echo "\n=== Selesai ===\n";
echo "\nVerifikasi:\n";
echo "- Cek symlink: ls -la public_html/storage\n";
echo "- Cek file: ls -la public_html/\n";
echo "- Test akses: curl -I https://posyandukaranggan.com/storage/logo_posyandu/java_1769452630.png\n";
