<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\AbstractMeasure;
use Oct8pus\NanoTimer\MemoryMeasure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AbstractMeasure::class)]
#[CoversClass(MemoryMeasure::class)]
final class MemoryMeasureTest extends TestCase
{
    public function test() : void
    {
        $label = 'test';
        $memory = memory_get_peak_usage(true);

        $measure = new MemoryMeasure($label);

        self::assertSame($label, $measure->label());
        self::assertSame($memory, $measure->memory());

        $value = (string) round($memory / (1024 * 1024), 1, PHP_ROUND_HALF_UP) . 'MB';

        self::assertSame($value, $measure->valueStr());
        self::assertSame("{$label}: {$value}", $measure->colon());
        self::assertSame("{$label}    {$value}", $measure->pad(strlen($label) + 2, strlen($value) + 2));
    }
}
