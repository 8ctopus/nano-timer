<?php

declare(strict_types=1);

use Oct8pus\NanoTimer\NanoTimer;

// allows to check autoload time
$hrtime = hrtime(true);

require_once 'vendor/autoload.php';

$timer = new NanoTimer($hrtime, null);

$timer->measure('constructor');

usleep(200000);

$timer->measure('usleep 200ms');

foreach (range(0, 50000) as $i) {
    $a = $i * $i;
}

$timer->measure('pow range 0-50000');

echo $timer->log(true);
