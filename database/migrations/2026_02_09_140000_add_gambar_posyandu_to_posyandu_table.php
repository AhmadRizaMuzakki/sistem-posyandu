<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Gambar ini ditampilkan di halaman detail posyandu (publik) di atas peta.
     */
    public function up(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            if (!Schema::hasColumn('posyandu', 'gambar_posyandu')) {
                $table->string('gambar_posyandu')->nullable()->after('logo_posyandu');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            if (Schema::hasColumn('posyandu', 'gambar_posyandu')) {
                $table->dropColumn('gambar_posyandu');
            }
        });
    }
};
