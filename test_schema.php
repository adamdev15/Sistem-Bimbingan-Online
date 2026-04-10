<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fks = Illuminate\Support\Facades\Schema::getForeignKeys('kehadirans');
$idxs = Illuminate\Support\Facades\Schema::getIndexes('kehadirans');

echo "FOREIGN KEYS:\n";
print_r(array_map(fn($k) => $k['name'], $fks));

echo "\nINDEXES:\n";
print_r(array_map(fn($k) => $k['name'], $idxs));
