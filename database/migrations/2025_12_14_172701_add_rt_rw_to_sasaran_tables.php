<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah RT dan RW ke semua tabel sasaran
        $tables = [
            'sasaran_bayibalita',
            'sasaran_remajas',
            'sasaran_ibuhamils',
            'sasaran_dewasas',
            'sasaran_pralansias',
            'sasaran_lansias',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('rt')->nullable()->after('alamat_sasaran');
                    $table->string('rw')->nullable()->after('rt');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'sasaran_bayibalita',
            'sasaran_remajas',
            'sasaran_ibuhamils',
            'sasaran_dewasas',
            'sasaran_pralansias',
            'sasaran_lansias',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['rt', 'rw']);
                });
            }
        }
    }
};
