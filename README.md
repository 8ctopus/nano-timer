# nano timer

[![packagist](http://poser.pugx.org/8ctopus/nano-timer/v)](https://packagist.org/packages/8ctopus/nano-timer)
[![downloads](http://poser.pugx.org/8ctopus/nano-timer/downloads)](https://packagist.org/packages/8ctopus/nano-timer)
[![min php version](http://poser.pugx.org/8ctopus/nano-timer/require/php)](https://packagist.org/packages/8ctopus/nano-timer)
[![license](http://poser.pugx.org/8ctopus/nano-timer/license)](https://packagist.org/packages/8ctopus/nano-timer)
[![tests](https://github.com/8ctopus/nano-timer/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/nano-timer/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/nano-timer/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/nano-timer/image-data/lines.svg)

Yet another php timer

## why another timer?

The main reason for this timer is to analyze slow requests that occur from time to time in production where using tools such as XDebug is not advisable.

## features

- measure time between events
- log only requests slower than a given threshold
- automatically log when the destructor is called
- measure peak memory use

## install

- `composer require 8ctopus/nano-timer`

## simple example

```php
use Oct8pus\NanoTimer\NanoTimer;

require_once __DIR__ . '/vendor/autoload.php';

$timer = new NanoTimer();

usleep(200000);

$timer->measure('usleep 200ms');

foreach (range(0, 50000) as $i) {
    $a = $i * $i;
}

$timer->measure('range 0-50000');

echo $timer->table();
```

```txt
usleep 200ms     211ms
range 0-50000     12ms
total            223ms
```

## more advanced

- log autoload and constructor time
- log peak memory use

```php
use Oct8pus\NanoTimer\NanoTimer;

// autoload and constructor time
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

echo $timer->table();
```

```txt
autoload and constructor   23ms
200ms sleep               211ms
1s sleep                 1012ms
pow range 0-50000          13ms
total                    1259ms
memory peak use             4MB
```

## only log measurements slower than

It's sometimes useful to only log measurements slower than a given threshold.

```php
$timer = new NanoTimer();

$timer
    ->logSlowerThan(100)
    ->autoLog();

...
```

In this example, the request will automatically be logged to the error log when the destructor is called if the total time spent is more than 100 milliseconds.

```txt
destruct: 614ms - total: 614ms
```

## run tests

    composer test

## clean code

    composer fix(-risky)
