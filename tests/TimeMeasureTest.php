<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\TimeMeasure;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\NanoTimer\AbstractMeasure
 * @covers \Oct8pus\NanoTimer\TimeMeasure
 */
final class TimeMeasureTest extends TestCase
{
    public function test() : void
    {
        $label = 'test';
        $hrtime = 11000000;
        $ms = round($hrtime / 1000000, 0, PHP_ROUND_HALF_UP);

        $measure = new TimeMeasure($label, $hrtime);

        self::assertSame($label, $measure->label());
        self::assertSame($hrtime, $measure->hrtime());
        self::assertSame($ms, $measure->ms());

        $value = "{$ms}ms";

        self::assertSame($value, $measure->value());
        self::assertSame("{$label}: {$value}", $measure->colon());
        self::assertSame("{$label}    {$value}", $measure->pad(strlen($label) + 2, strlen($value) + 2));
    }
}
