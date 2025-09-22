<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\AbstractMeasure;
use Oct8pus\NanoTimer\TimeMeasure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AbstractMeasure::class)]
#[CoversClass(TimeMeasure::class)]
final class TimeMeasureTest extends TestCase
{
    public function test() : void
    {
        $label = 'test';
        $hrtime = 11000000;
        $ms = (int) round($hrtime / 1000000, 0, PHP_ROUND_HALF_UP);

        $measure = new TimeMeasure($label, $hrtime);

        self::assertSame($label, $measure->label());
        self::assertSame($hrtime, $measure->delta());
        self::assertSame($ms, $measure->value());

        $value = "{$ms}ms";

        self::assertSame($value, $measure->valueStr());
        self::assertSame("{$label}: {$value}", $measure->colon());
        self::assertSame("{$label}    {$value}", $measure->pad(strlen($label) + 2, strlen($value) + 2));
    }
}
