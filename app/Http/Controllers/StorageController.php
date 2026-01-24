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
        $path = str_replace('\\', '/', $path);

        // Cegah path traversal
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
