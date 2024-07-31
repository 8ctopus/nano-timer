<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\NanoVariability;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\NanoTimer\NanoVariability
 */
final class NanoVariabilityTest extends TestCase
{
    public function testTable() : void
    {
        $variability = new NanoVariability();

        usleep(20000);

        $variability->measure('lap 1');
        $lap1 = $variability->last()->ms();

        usleep((int) (20000 * 0.95));

        $variability->measure('lap 2');
        $lap2 = $variability->last()->ms();

        usleep((int) (20000 * 1.10));

        $variability->measure('lap 2');
        $lap3 = $variability->last()->ms();

        $average = round(($lap1 + $lap2 + $lap3) / 3, 0, PHP_ROUND_HALF_UP);

        $values = [$lap1, $lap2, $lap3];

        sort($values);

        $median = $values[1];

        $output = <<<OUTPUT
        lap 1   {$lap1}ms
        lap 2   {$lap2}ms
        lap 2   {$lap3}ms
        average {$average}ms
        median  {$median}ms
        minimum {$values[0]}ms
        maximum {$values[2]}ms

        OUTPUT;

        self::assertSame($output, $variability->table());
        self::assertSame($output, (string) $variability);

        $output = <<<OUTPUT
        average {$average}ms
        median  {$median}ms
        minimum {$values[0]}ms
        maximum {$values[2]}ms

        OUTPUT;

        self::assertSame($output, $variability->table(false));
    }
}
