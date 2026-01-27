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
        if (!Schema::hasTable('pendidikans')) {
            return;
        }

        Schema::table('pendidikans', function (Blueprint $table) {
            if (!Schema::hasColumn('pendidikans', 'rt')) {
                $table->string('rt')->nullable()->after('umur');
            }
            if (!Schema::hasColumn('pendidikans', 'rw')) {
                $table->string('rw')->nullable()->after('rt');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendidikans', function (Blueprint $table) {
            if (Schema::hasColumn('pendidikans', 'rt')) {
                $table->dropColumn('rt');
            }
            if (Schema::hasColumn('pendidikans', 'rw')) {
                $table->dropColumn('rw');
            }
        });
    }
};
