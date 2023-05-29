<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\NanoTimer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\NanoTimer\NanoTimer
 */
final class NanoTimerTest extends TestCase
{
    public function testLine() : void
    {
        $timer = new NanoTimer();

        $microtime = microtime(true);

        time_sleep_until($microtime + 0.1);

        $timer->measure('100ms sleep');

        $delta = round((microtime(true) - $microtime) * 1000, 0, PHP_ROUND_HALF_UP);

        $output = "100ms sleep: {$delta}ms - total: {$delta}ms";

        static::assertEquals($output, $timer->line());
    }

    public function testTable() : void
    {
        $timer = new NanoTimer(hrtime(true));

        $timer->measure('constructor');

        $microtime = microtime(true);

        time_sleep_until($microtime + 0.1);

        $timer->measure('100ms sleep');

        $delta = round((microtime(true) - $microtime) * 1000, 0, PHP_ROUND_HALF_UP);

        $output = <<<OUTPUT
        constructor    0ms
        100ms sleep  {$delta}ms
        total        {$delta}ms

        OUTPUT;

        static::assertEquals($output, $timer->table());
        static::assertEquals($output, (string) $timer);
    }

    public function testPeakUse() : void
    {
        $timer = new NanoTimer();

        $timer->logMemoryPeakUse();

        $used = memory_get_peak_usage(true);
        $used = round($used / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

        $output = <<<OUTPUT
        total              0ms
        memory peak use   {$used}MB

        OUTPUT;

        static::assertEquals($output, $timer->table());
    }

    public function testLogSlowerThan() : void
    {
        $timer = new NanoTimer();

        $timer->logSlowerThan(50);

        $timer->measure('fast request');

        static::assertEmpty($timer->table());
        static::assertEmpty($timer->line());

        usleep(51000);

        $timer->measure('slow request');

        static::assertNotEmpty($timer->table());
        static::assertNotEmpty($timer->line());
    }

    public function testAutoLog() : void
    {
        static::expectOutputString('test: 0ms - destruct: 0ms - total: 0ms');

        try {
            $timer = new NanoTimerMock();

            $timer
                ->autoLog()
                ->measure('test');

            unset($timer);
        } finally {
        }
    }
}

class NanoTimerMock extends NanoTimer
{
    protected function errorLog(string $log) : self
    {
        echo($log);
        return $this;
    }
}