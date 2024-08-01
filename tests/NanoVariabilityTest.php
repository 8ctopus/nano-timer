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

        self::assertEmpty($variability->table());

        usleep(20000);

        $variability->measure('lap 1');
        $lap1 = $variability->last()->delta();

        usleep((int) (20000 * 0.95));

        $variability->measure('lap 2');
        $lap2 = $variability->last()->delta();

        usleep((int) (20000 * 1.10));

        $variability->measure('lap 3');
        $lap3 = $variability->last()->delta();

        $values = [$lap1, $lap2, $lap3];

        sort($values);

        $average = round(array_sum($values) / count($values) / 1000000, 0, PHP_ROUND_HALF_UP);
        $median = round($values[1] / 1000000, 0, PHP_ROUND_HALF_UP);
        $min = round($values[0] / 1000000, 0, PHP_ROUND_HALF_UP);
        $max = round(end($values) / 1000000, 0, PHP_ROUND_HALF_UP);

        $lap1 = round($lap1 / 1000000);
        $lap2 = round($lap2 / 1000000);
        $lap3 = round($lap3 / 1000000);

        $output = <<<OUTPUT
        lap 1   {$lap1}ms
        lap 2   {$lap2}ms
        lap 3   {$lap3}ms
        average {$average}ms
        median  {$median}ms
        minimum {$min}ms
        maximum {$max}ms

        OUTPUT;

        self::assertSame($output, $variability->table());
        self::assertSame($output, (string) $variability);

        $output = <<<OUTPUT
        average {$average}ms
        median  {$median}ms
        minimum {$min}ms
        maximum {$max}ms

        OUTPUT;

        self::assertSame($output, $variability->table(false));

        usleep((int) (20000 * 0.85));

        $variability->measure('lap 4');
        $lap4 = $variability->last()->delta();

        $values[] = $lap4;

        sort($values);

        $average = round(array_sum($values) / count($values) / 1000000, 0, PHP_ROUND_HALF_UP);
        $median = round(($values[1] + $values[2]) / 2 / 1000000, 0, PHP_ROUND_HALF_UP);
        $min = round($values[0] / 1000000, 0, PHP_ROUND_HALF_UP);
        $max = round(end($values) / 1000000, 0, PHP_ROUND_HALF_UP);

        $lap4 = round($lap4 / 1000000);

        $output = <<<OUTPUT
        lap 1   {$lap1}ms
        lap 2   {$lap2}ms
        lap 3   {$lap3}ms
        lap 4   {$lap4}ms
        average {$average}ms
        median  {$median}ms
        minimum {$min}ms
        maximum {$max}ms

        OUTPUT;

        self::assertSame($output, $variability->table());
        self::assertSame($output, (string) $variability);

        $variability->reset(true);
        self::assertEmpty($variability->table());
    }
}
