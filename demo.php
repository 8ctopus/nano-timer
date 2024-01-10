<?php

declare(strict_types=1);

use Oct8pus\NanoTimer\NanoTimer;

// to check autoload and nano timer constructor time
$hrtime = hrtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$timer = new NanoTimer($hrtime);

$timer
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

echo $timer->last() . PHP_EOL;

echo $timer->table();
