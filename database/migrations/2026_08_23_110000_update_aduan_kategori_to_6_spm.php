<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('aduans')) {
            return;
        }

        $map = [
            'layanan' => 'kesehatan',
            'fasilitas' => 'pekerjaan_umum',
            'kader' => 'sosial',
            'data' => 'pendidikan',
            'lainnya' => 'sosial',
        ];

        foreach ($map as $old => $new) {
            DB::table('aduans')->where('kategori', $old)->update(['kategori' => $new]);
        }

        DB::statement("ALTER TABLE aduans MODIFY kategori ENUM(
            'kesehatan',
            'pendidikan',
            'pekerjaan_umum',
            'perumahan_rakyat',
            'trantibumlinmas',
            'sosial'
        ) NOT NULL DEFAULT 'kesehatan'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('aduans')) {
            return;
        }

        $map = [
            'kesehatan' => 'layanan',
            'pendidikan' => 'data',
            'pekerjaan_umum' => 'fasilitas',
            'perumahan_rakyat' => 'fasilitas',
            'trantibumlinmas' => 'kader',
            'sosial' => 'lainnya',
        ];

        foreach ($map as $old => $new) {
            DB::table('aduans')->where('kategori', $old)->update(['kategori' => $new]);
        }

        DB::statement("ALTER TABLE aduans MODIFY kategori ENUM(
            'layanan',
            'fasilitas',
            'kader',
            'data',
            'lainnya'
        ) NOT NULL DEFAULT 'lainnya'");
    }
};
