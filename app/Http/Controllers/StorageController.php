<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menyajikan file dari storage/app/public via PHP.
 * Dipakai saat symlink/exec disabled (mis. Hostinger) sehingga storage:link gagal.
 */
class StorageController extends Controller
{
    /**
     * Serve file dari disk 'public' (storage/app/public).
     * URL: /storage/{path} contoh /storage/sk_posyandu/file.pdf
     */
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        $path = str_replace('\\', '/', trim($path, "/ \t\n\r\0\x0B"));

        // Cegah path traversal: .. , %2e%2e, dll
        if (str_contains($path, '..') || str_contains($path, '%2e') || str_contains($path, '%252e')) {
            abort(404);
        }

        // Decode URL-encoding sekali (hindari double decode bypass)
        $path = rawurldecode($path);
        if (str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);
        $realPath = realpath($fullPath);
        $rootPath = realpath($disk->path(''));
        if ($realPath === false || $rootPath === false || ! str_starts_with($realPath . DIRECTORY_SEPARATOR, $rootPath . DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        return $disk->response($path);
    }
}
