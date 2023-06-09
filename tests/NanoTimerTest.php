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

        $output = "total: {$delta}ms - 100ms sleep: {$delta}ms";

        self::assertSame($output, $timer->line());
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

        self::assertSame($output, $timer->table());
        self::assertSame($output, (string) $timer);
    }

    public function testPeakUse() : void
    {
        $timer = new NanoTimer();

        $timer->logMemoryPeakUse();

        $used = memory_get_peak_usage(true);
        $used = (string) round($used / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

        if (strlen($used) === 1) {
            $used = ' ' . $used;
        }

        $output = <<<OUTPUT
        total              0ms
        memory peak use   {$used}MB

        OUTPUT;

        self::assertSame($output, $timer->table());
    }

    public function testLogSlowerThan() : void
    {
        $timer = new NanoTimer();

        $timer
            ->logSlowerThan(50)
            ->measure('fast request');

        self::assertEmpty($timer->table());
        self::assertEmpty($timer->line());

        usleep(51000);

        $timer->measure('slow request');

        self::assertNotEmpty($timer->table());
        self::assertNotEmpty($timer->line());
    }

    public function testAutoLog() : void
    {
        $timer = new NanoTimerMock();

        $timer
            ->autoLog()
            ->measure('test');

        self::expectOutputString('nanotimer - total: 0ms - test: 0ms - destruct: 0ms');
    }

    public function testAutoLog2() : void
    {
        $timer = new NanoTimerMock();

        $timer
            ->setLabel('hello world')
            ->autoLog()
            ->measure('test');

        self::expectOutputString('hello world - total: 0ms - test: 0ms - destruct: 0ms');
    }

    public function testAutoLogPeakUseMemory() : void
    {
        $timer = new NanoTimerMock();

        $timer
            ->autoLog()
            ->logMemoryPeakUse()
            ->measure('test');

        $used = memory_get_peak_usage(true);
        $used = round($used / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

        self::expectOutputString("nanotimer - total: 0ms - test: 0ms - destruct: 0ms - memory peak use: {$used}MB");
    }
}

class NanoTimerMock extends NanoTimer
{
    protected function errorLog(string $message) : void
    {
        echo $message;
    }
}
