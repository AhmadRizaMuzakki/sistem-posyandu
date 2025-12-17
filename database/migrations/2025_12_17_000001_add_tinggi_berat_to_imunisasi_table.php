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
        if (Schema::hasTable('imunisasi')) {
            Schema::table('imunisasi', function (Blueprint $table) {
                // Simpan tinggi badan dalam sentimeter dan berat badan dalam kilogram
                $table->decimal('tinggi_badan', 5, 2)->nullable()->after('tanggal_imunisasi');
                $table->decimal('berat_badan', 5, 2)->nullable()->after('tinggi_badan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('imunisasi')) {
            Schema::table('imunisasi', function (Blueprint $table) {
                if (Schema::hasColumn('imunisasi', 'berat_badan')) {
                    $table->dropColumn('berat_badan');
                }

                if (Schema::hasColumn('imunisasi', 'tinggi_badan')) {
                    $table->dropColumn('tinggi_badan');
                }
            });
        }
    }
};


