<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tableName(): ?string
    {
        if (Schema::hasTable('spm')) {
            return 'spm';
        }

        if (Schema::hasTable('aduans')) {
            return 'aduans';
        }

        return null;
    }

    public function up(): void
    {
        $table = $this->tableName();
        if (! $table) {
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
            DB::table($table)->where('kategori', $old)->update(['kategori' => $new]);
        }

        DB::statement("ALTER TABLE {$table} MODIFY kategori ENUM(
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
        $table = $this->tableName();
        if (! $table) {
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
            DB::table($table)->where('kategori', $old)->update(['kategori' => $new]);
        }

        DB::statement("ALTER TABLE {$table} MODIFY kategori ENUM(
            'layanan',
            'fasilitas',
            'kader',
            'data',
            'lainnya'
        ) NOT NULL DEFAULT 'lainnya'");
    }
};
