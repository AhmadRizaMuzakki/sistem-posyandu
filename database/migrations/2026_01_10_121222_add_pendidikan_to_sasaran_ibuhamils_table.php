<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sasaran_ibuhamils')) {
            return;
        }

        if (!Schema::hasColumn('sasaran_ibuhamils', 'pendidikan')) {
            Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
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
                    'Strata III'
                ])->nullable()->after('pekerjaan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sasaran_ibuhamils', 'pendidikan')) {
            Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
                $table->dropColumn('pendidikan');
            });
        }
    }
};
