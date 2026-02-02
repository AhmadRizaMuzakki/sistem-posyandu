<?php

if (!function_exists('safe_upload_extension')) {
    /**
     * Ambil ekstensi aman dari file upload berdasarkan MIME (whitelist).
     * Hindari getClientOriginalExtension() yang bisa dipalsu.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param array<string, string> $mimeToExt Map MIME => extension, contoh ['image/jpeg' => 'jpg']
     * @return string|null Extension jika MIME diwhitelist, null jika tidak
     */
    function safe_upload_extension($file, array $mimeToExt): ?string
    {
        $mime = $file->getMimeType();
        return $mimeToExt[$mime] ?? null;
    }
}

if (!function_exists('uploads_safe_full_path')) {
    /**
     * Resolve path file upload dengan validasi anti path traversal.
     *
     * @param string $relativePath Path relatif dari uploads/, contoh 'galeri/xxx.jpg'
     * @return string|null Full path jika aman dan ada, null jika traversal/salah
     */
    function uploads_safe_full_path(string $relativePath): ?string
    {
        $path = str_replace('\\', '/', trim($relativePath, "/ \t\n\r"));
        if (str_contains($path, '..') || str_contains($path, '%2e')) {
            return null;
        }
        $base = public_path('uploads');
        $full = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        $real = realpath($full);
        $baseReal = realpath($base);
        if ($real === false || $baseReal === false || ! str_starts_with($real . DIRECTORY_SEPARATOR, $baseReal . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $real;
    }
}

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
