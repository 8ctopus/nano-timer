<?php

declare(strict_types=1);

use Oct8pus\NanoTimer\Compare;
use Oct8pus\NanoTimer\NanoTimer;
use Oct8pus\NanoTimer\NanoVariability;

// to check autoload and nano timer constructor time
$hrtime = hrtime(true);

require_once __DIR__ . '/vendor/autoload.php';

echo "measure performance\n\n";

$timer = (new NanoTimer($hrtime))
    ->logSlowerThan(0)
    ->logMemoryPeakUse(true);

$timer->measure('autoload and constructor');

usleep(200 * 1000);

$timer->measure('200ms sleep');

sleep(1);

$timer->measure('1s sleep');

foreach (range(0, 50000) as $i) {
    $a = $i * $i;
}

$timer->measure('pow range 0-50000');

echo $timer->table(true) . "\n";
echo $timer->line() . "\n";

echo "\nmeasure variability\n\n";

$variability1 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    $ms = (1000 + rand(0, +200)) * 10;
    usleep($ms);
    $variability1->measure("lap {$i}");
}

echo $variability1->table(true) . "\n";

$variability2 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    $ms = (1000 + rand(0, +300)) * 10;
    usleep($ms);
    $variability2->measure("lap {$i}");
}

echo $variability2->table(true) . "\n";

echo "compare\n\n";

$compare = new Compare($variability1, $variability2);

echo $compare->table(true);
