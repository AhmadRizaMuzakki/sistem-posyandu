<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 15;
$fixed = App\Services\PendidikanChartService::repairYoungChildrenPendidikan($id);
$synced = App\Services\PendidikanChartService::syncFromSasaran($id);
echo "fixed=$fixed synced=$synced" . PHP_EOL;
print_r(App\Models\Pendidikan::where('id_posyandu', $id)->selectRaw('pendidikan_terakhir, count(*) c')->groupBy('pendidikan_terakhir')->pluck('c', 'pendidikan_terakhir')->toArray());
echo "total=" . App\Models\Pendidikan::where('id_posyandu', $id)->count() . PHP_EOL;
$youngWrong = App\Models\Pendidikan::where('id_posyandu', $id)->where('umur', '<=', 6)->where('pendidikan_terakhir', 'Tamat SD/Sederajat')->count();
echo "young still Tamat SD=$youngWrong" . PHP_EOL;
