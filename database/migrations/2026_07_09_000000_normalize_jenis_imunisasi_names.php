<?php

use App\Models\Imunisasi;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (\App\Helpers\ImunisasiOptions::legacyNameMap() as $old => $new) {
            Imunisasi::where('jenis_imunisasi', $old)->update(['jenis_imunisasi' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (\App\Helpers\ImunisasiOptions::legacyNameMap() as $old => $new) {
            Imunisasi::where('jenis_imunisasi', $new)->update(['jenis_imunisasi' => $old]);
        }
    }
};
