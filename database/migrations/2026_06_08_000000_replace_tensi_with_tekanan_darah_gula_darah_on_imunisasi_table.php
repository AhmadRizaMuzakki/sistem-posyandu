<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->string('tekanan_darah', 20)->nullable()->after('berat_badan')->comment('format: 120/80 mmHg');
            $table->decimal('gula_darah', 6, 2)->nullable()->after('tekanan_darah')->comment('mg/dL');
        });

        DB::table('imunisasi')
            ->whereNotNull('sistol')
            ->whereNotNull('diastol')
            ->orderBy('id_imunisasi')
            ->each(function ($row) {
                DB::table('imunisasi')
                    ->where('id_imunisasi', $row->id_imunisasi)
                    ->update(['tekanan_darah' => $row->sistol . '/' . $row->diastol]);
            });

        Schema::table('imunisasi', function (Blueprint $table) {
            $table->dropColumn(['sistol', 'diastol']);
        });
    }

    public function down(): void
    {
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->unsignedSmallInteger('sistol')->nullable()->after('berat_badan')->comment('mmHg');
            $table->unsignedSmallInteger('diastol')->nullable()->after('sistol')->comment('mmHg');
        });

        DB::table('imunisasi')
            ->whereNotNull('tekanan_darah')
            ->orderBy('id_imunisasi')
            ->each(function ($row) {
                if (preg_match('/^(\d+)\/(\d+)$/', $row->tekanan_darah, $matches)) {
                    DB::table('imunisasi')
                        ->where('id_imunisasi', $row->id_imunisasi)
                        ->update([
                            'sistol' => (int) $matches[1],
                            'diastol' => (int) $matches[2],
                        ]);
                }
            });

        Schema::table('imunisasi', function (Blueprint $table) {
            $table->dropColumn(['tekanan_darah', 'gula_darah']);
        });
    }
};
