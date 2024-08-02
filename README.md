# nano timer

[![packagist](https://poser.pugx.org/8ctopus/nano-timer/v)](https://packagist.org/packages/8ctopus/nano-timer)
[![downloads](https://poser.pugx.org/8ctopus/nano-timer/downloads)](https://packagist.org/packages/8ctopus/nano-timer)
[![min php version](https://poser.pugx.org/8ctopus/nano-timer/require/php)](https://packagist.org/packages/8ctopus/nano-timer)
[![license](https://poser.pugx.org/8ctopus/nano-timer/license)](https://packagist.org/packages/8ctopus/nano-timer)
[![tests](https://github.com/8ctopus/nano-timer/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/nano-timer/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/nano-timer/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/nano-timer/image-data/lines.svg)

Yet another php timer

## why another timer?

The main reason I built this timer was to analyze slow requests that occur from time to time on the production server where using tools such as [XDebug](https://github.com/xdebug/xdebug) or [php SPX](https://github.com/NoiseByNorthwest/php-spx) is not advisable.

## features

- measure timing between events
- measure variability for the same code loop
- compare results side by side
- log only requests slower than a given threshold
- automatically log when the destructor is called
- measure peak memory use

## install

- `composer require 8ctopus/nano-timer`

## demo

There's `demo.php` in the project root directory that showcases most examples below.

## simple timing measurement

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
usleep 200ms  211ms
range 0-50000  12ms
total         223ms
```

## more advanced timing

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

It's sometimes useful to only log measurements slower than a given threshold. In this example, the request will automatically be logged to the error log when the destructor is called if the total time spent is more than 100 milliseconds.

```php
$timer = new NanoTimer();

$timer
    ->logSlowerThan(100)
    ->autoLog();

...
```
```txt
nanotimer - total: 614ms - destruct: 614ms
```

## measure variability

Sometimes you need to understand the speed variability of the same code loop.

```php
$variability1 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    $ms = (1000 + rand(0, +200)) * 10;
    usleep($ms);
    $variability1->measure("lap {$i}");
}

echo $variability1->table(true) . "\n";
```

```txt
lap 1   13ms
lap 2   16ms
lap 3   16ms
lap 4   16ms
lap 5   15ms
average 15ms
median  16ms
minimum 13ms
maximum 16ms
```

## compare

Results can be compared both for timings and variability.

```php
$v1 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    usleep(500 + rand(0, 100));
    $v1->measure("lap {$i}");
}

$v2 = new NanoVariability();

for ($i = 1; $i < 6; ++$i) {
    usleep(500 + rand(0, 100));
    $v2->measure("lap {$i}");
}

$compare = new Compare($v1, $v2);
```

```txt
compare
lap 1     7 13 +86%
lap 2    15 15 +0%
lap 3    15 15 +0%
lap 4    15 15 +0%
lap 5    15 16 +7%
average  13 15 +15%
median   15 15 +0%
minimum   7 13 +86%
maximum  15 16 +7%
```
