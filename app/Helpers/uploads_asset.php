<?php

if (!function_exists('uploads_base_path')) {
    /**
     * Path dasar folder upload (sama dengan Laravel public).
     * Jika public adalah symlink ke public_html, path ini mengikuti symlink.
     */
    function uploads_base_path(string $path = ''): string
    {
        $base = public_path();
        return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path, '/\\'));
    }
}

if (!function_exists('uploads_asset')) {
    /**
     * URL untuk file yang disimpan di public/uploads/ atau (backward) di storage.
     * Path di DB bisa: 'galeri/xx.png', 'foto_kader/xx.jpg', '/storage/galeri/xx.png'.
     */
    function uploads_asset(?string $path): string
    {
        if (!$path) {
            return asset('');
        }
        $p = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($p, 'storage/')) {
            return asset($p);
        }
        return asset('uploads/' . $p);
    }
}
