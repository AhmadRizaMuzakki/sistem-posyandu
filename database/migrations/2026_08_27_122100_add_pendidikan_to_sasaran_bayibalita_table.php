<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sasaran_bayibalita')) {
            return;
        }

        if (! Schema::hasColumn('sasaran_bayibalita', 'pendidikan')) {
            Schema::table('sasaran_bayibalita', function (Blueprint $table) {
                $table->enum('pendidikan', [
                    'Tidak/Belum Sekolah',
                    'PAUD',
                    'TK',
                    'Tidak Tamat SD/Sederajat',
                    'Tamat SD/Sederajat',
                    'SLTP/Sederajat',
                    'SLTA/Sederajat',
                    'Diploma I/II',
                    'Akademi/Diploma III/Sarjana Muda',
                    'Diploma IV/Strata I',
                    'Strata II',
                    'Strata III',
                ])->default('Tidak/Belum Sekolah')->nullable()->after('umur_sasaran');
            });
        }

        DB::table('sasaran_bayibalita')
            ->whereNull('pendidikan')
            ->update(['pendidikan' => 'Tidak/Belum Sekolah']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sasaran_bayibalita', 'pendidikan')) {
            Schema::table('sasaran_bayibalita', function (Blueprint $table) {
                $table->dropColumn('pendidikan');
            });
        }
    }
};
