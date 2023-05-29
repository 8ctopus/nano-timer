<?php

declare(strict_types=1);

use Oct8pus\NanoTimer\NanoTimer;

// allows to check autoload time
$hrtime = hrtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$timer = new NanoTimer($hrtime);

$timer->measure('constructor');

usleep(200000);

$timer->measure('sleep 200ms');

sleep(1);

$timer->measure('sleep 1s');

foreach (range(0, 50000) as $i) {
    $a = $i * $i;
}

$timer->measure('pow range 0-50000');

echo $timer->report(true);
