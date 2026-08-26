<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        if (! $table || Schema::hasColumn($table, 'no_surat_permohonan_rt')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->string('no_surat_permohonan_rt', 100)->nullable()->after('isi_aduan');
        });
    }

    public function down(): void
    {
        $table = $this->tableName();
        if (! $table || ! Schema::hasColumn($table, 'no_surat_permohonan_rt')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('no_surat_permohonan_rt');
        });
    }
};
