<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration untuk menambah index pada kolom yang sering di-query
 * Ini akan mempercepat query terutama untuk:
 * - Filter berdasarkan id_posyandu
 * - Filter berdasarkan kategori_sasaran + id_sasaran
 * - Filter berdasarkan no_kk_sasaran
 */
return new class extends Migration
{
    /**
     * Cek apakah index sudah ada
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Tambah index jika belum ada
     */
    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                $table->index($column, $indexName);
            });
        }
    }

    public function up(): void
    {
        // Index untuk tabel imunisasi
        if (!$this->indexExists('imunisasi', 'imunisasi_tanggal_idx')) {
            Schema::table('imunisasi', function (Blueprint $table) {
                $table->index('tanggal_imunisasi', 'imunisasi_tanggal_idx');
            });
        }

        // Index untuk tabel sasaran_bayibalita
        $this->addIndexIfNotExists('sasaran_bayibalita', 'no_kk_sasaran', 'sasaran_bayibalita_nokk_idx');
        $this->addIndexIfNotExists('sasaran_bayibalita', 'nik_sasaran', 'sasaran_bayibalita_nik_idx');

        // Index untuk tabel sasaran_remajas
        $this->addIndexIfNotExists('sasaran_remajas', 'no_kk_sasaran', 'sasaran_remaja_nokk_idx');
        $this->addIndexIfNotExists('sasaran_remajas', 'nik_sasaran', 'sasaran_remaja_nik_idx');

        // Index untuk tabel sasaran_dewasas
        $this->addIndexIfNotExists('sasaran_dewasas', 'no_kk_sasaran', 'sasaran_dewasa_nokk_idx');
        $this->addIndexIfNotExists('sasaran_dewasas', 'nik_sasaran', 'sasaran_dewasa_nik_idx');

        // Index untuk tabel sasaran_pralansias
        $this->addIndexIfNotExists('sasaran_pralansias', 'no_kk_sasaran', 'sasaran_pralansia_nokk_idx');
        $this->addIndexIfNotExists('sasaran_pralansias', 'nik_sasaran', 'sasaran_pralansia_nik_idx');

        // Index untuk tabel sasaran_lansias
        $this->addIndexIfNotExists('sasaran_lansias', 'no_kk_sasaran', 'sasaran_lansia_nokk_idx');
        $this->addIndexIfNotExists('sasaran_lansias', 'nik_sasaran', 'sasaran_lansia_nik_idx');

        // Index untuk tabel pendidikans
        if (!$this->indexExists('pendidikans', 'pendidikan_terakhir_idx')) {
            Schema::table('pendidikans', function (Blueprint $table) {
                $table->index('pendidikan_terakhir', 'pendidikan_terakhir_idx');
            });
        }

        // Index untuk tabel orangtua
        $this->addIndexIfNotExists('orangtua', 'tanggal_lahir', 'orangtua_tanggal_lahir_idx');
        $this->addIndexIfNotExists('orangtua', 'no_kk', 'orangtua_nokk_idx');
    }

    public function down(): void
    {
        // Drop indexes (ignore errors if they don't exist)
        $indexesToDrop = [
            'imunisasi' => ['imunisasi_tanggal_idx'],
            'sasaran_bayibalita' => ['sasaran_bayibalita_nokk_idx', 'sasaran_bayibalita_nik_idx'],
            'sasaran_remajas' => ['sasaran_remaja_nokk_idx', 'sasaran_remaja_nik_idx'],
            'sasaran_dewasas' => ['sasaran_dewasa_nokk_idx', 'sasaran_dewasa_nik_idx'],
            'sasaran_pralansias' => ['sasaran_pralansia_nokk_idx', 'sasaran_pralansia_nik_idx'],
            'sasaran_lansias' => ['sasaran_lansia_nokk_idx', 'sasaran_lansia_nik_idx'],
            'pendidikans' => ['pendidikan_terakhir_idx'],
            'orangtua' => ['orangtua_tanggal_lahir_idx', 'orangtua_nokk_idx'],
        ];

        foreach ($indexesToDrop as $table => $indexes) {
            foreach ($indexes as $indexName) {
                if ($this->indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $t) use ($indexName) {
                        $t->dropIndex($indexName);
                    });
                }
            }
        }
    }
};
