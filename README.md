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

The main reason for this timer is to analyze slow requests that occur from time to time in the production environment where using tools such as XDebug is not advisable.

## features

- measure time between events
- option to log only requests slower than a given threshold
- option to automatically log report when the destructor is called

## install

- `composer require 8ctopus/nano-timer`

```php
use Oct8pus\NanoTimer\NanoTimer;

// to check autoload and nano timer constructor time
$hrtime = hrtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$timer = new NanoTimer($hrtime);

usleep(200000);

$timer->measure('usleep 200ms');

foreach (range(0, 50000) as $i) {
    $a = $i * $i;
}

$timer->measure('range 0-50000');

echo $timer->report(true);
```

## run tests

    composer test

## clean code

    composer fix(-risky)
