<?php

use App\Helpers\EnumConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $transitionalList = array_merge(EnumConstants::pekerjaan(), [
            'Pegawai Negeri Sipil',
            'Kepolisian RI',
            'Penterjemah',
            'Anggota DPRD Kabupaten/Kota',
            'Duta Besar',
            'Bhikhu',
        ]);

        $this->alterEnum($transitionalList);

        DB::table('sasaran_ibuhamils')
            ->where('pekerjaan_suami', 'Pegawai Negeri Sipil')
            ->update(['pekerjaan_suami' => 'Aparatur Sipil Negara (ASN)']);

        DB::table('sasaran_ibuhamils')
            ->where('pekerjaan_suami', 'Kepolisian RI')
            ->update(['pekerjaan_suami' => 'Kepolisian RI (POLRI)']);

        DB::table('sasaran_ibuhamils')
            ->where('pekerjaan_suami', 'Penterjemah')
            ->update(['pekerjaan_suami' => 'Penerjemah']);

        DB::table('sasaran_ibuhamils')
            ->where('pekerjaan_suami', 'Anggota DPRD Kabupaten/Kota')
            ->update(['pekerjaan_suami' => 'Anggota DPRD Kab/Kota']);

        DB::table('sasaran_ibuhamils')
            ->where('pekerjaan_suami', 'Duta Besar')
            ->update(['pekerjaan_suami' => 'Duta Besar/Kepala Perwakilan']);

        $this->alterEnum(EnumConstants::pekerjaan());
    }

    private function alterEnum(array $values): void
    {
        $enumString = "'" . implode("', '", array_map(
            static fn (string $value): string => str_replace("'", "''", $value),
            $values
        )) . "'";

        DB::statement("ALTER TABLE `sasaran_ibuhamils` MODIFY COLUMN `pekerjaan_suami` ENUM($enumString) NULL");
    }

    public function down(): void
    {
        // Dibiarkan kosong.
    }
};
