<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kegiatan', function (Blueprint $table) {
            $table->string('tempat')->nullable()->after('nama_kegiatan');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kegiatan', function (Blueprint $table) {
            $table->dropColumn('tempat');
        });
    }
};
