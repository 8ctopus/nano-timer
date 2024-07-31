<?php

declare(strict_types=1);

use Oct8pus\NanoTimer\Compare;
use Oct8pus\NanoTimer\NanoTimer;
use Oct8pus\NanoTimer\NanoVariability;

// to check autoload and nano timer constructor time
$hrtime = hrtime(true);

require_once __DIR__ . '/vendor/autoload.php';

echo 'measure performance' . PHP_EOL;

$timer = (new NanoTimer($hrtime))
    ->logSlowerThan(0)
    ->logMemoryPeakUse(true);

$timer->measure('autoload and constructor');

usleep(200000);

$timer->measure('200ms sleep');

sleep(1);

$timer->measure('1s sleep');

foreach (range(0, 50000) as $i) {
    $a = $i * $i;
}

$timer->measure('pow range 0-50000');

echo $timer->table() . PHP_EOL;
echo $timer->line() . PHP_EOL;

echo PHP_EOL . 'measure variability' . PHP_EOL;

$v1 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    usleep(500 + rand(0, 100));
    $v1->measure("lap {$i}");
}

echo $v1->table() . PHP_EOL;

$v2 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    usleep(500 + rand(0, 100));
    $v2->measure("lap {$i}");
}

echo $v2->table() . PHP_EOL;

echo 'compare' . PHP_EOL;

$compare = new Compare($v1, $v2);

echo $compare->table(true);
