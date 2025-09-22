<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\AbstractMeasures;
use Oct8pus\NanoTimer\MemoryMeasure;
use Oct8pus\NanoTimer\NanoTimer;
use Oct8pus\NanoTimer\TimeMeasure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AbstractMeasures::class)]
#[CoversClass(MemoryMeasure::class)]
#[CoversClass(NanoTimer::class)]
#[CoversClass(TimeMeasure::class)]
final class NanoTimerTest extends TestCase
{
    public function testEmpty() : void
    {
        $timer = new NanoTimer();

        self::assertSame('', $timer->line());
    }

    public function testLine() : void
    {
        $timer = new NanoTimer();

        $sleep = 100;

        usleep($sleep * 1000);

        $timer->measure("{$sleep}ms sleep");
        $delta = $timer->last()->value();

        self::assertSame("total: {$delta}ms - {$sleep}ms sleep: {$delta}ms", $timer->line());
        //self::assertTrue(abs($delta - $sleep) < 5);
    }

    public function testTable() : void
    {
        $timer = new NanoTimer(hrtime(true));

        $timer->measure('constructor');

        $microtime = microtime(true);

        time_sleep_until($microtime + 0.1);

        $timer->measure('100ms sleep');

        $delta = round((microtime(true) - $microtime) * 1000, 0, PHP_ROUND_HALF_UP);

        $table = $timer->table();

        $output = <<<OUTPUT
        constructor   0ms
        100ms sleep {$delta}ms
        total       {$delta}ms

        OUTPUT;

        self::assertSame($output, $table);
        self::assertSame($output, (string) $timer);
    }

    public function testPeakUse() : void
    {
        $timer = new NanoTimer();

        $timer
            ->logMemoryPeakUse()
            ->measure('test');

        $used = memory_get_peak_usage(true);
        $used = (string) round($used / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

        $space = strlen($used) === 2 ? ' ' : '';

        $output = <<<OUTPUT
        test            {$space}0ms
        total           {$space}0ms
        memory peak use {$used}MB

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

    public function testTotal() : void
    {
        $timer = new NanoTimer(hrtime(true));

        $microtime = microtime(true);
        time_sleep_until($microtime + 0.1);

        $timer->measure('100ms sleep');

        $delta = round((microtime(true) - $microtime) * 1000, 0, PHP_ROUND_HALF_UP);

        $output = "{$delta}ms";

        self::assertSame($output, $timer->total()->valueStr());
    }

    public function testLastMeasure() : void
    {
        $time = hrtime(true);

        $timer = new NanoTimerMock($time);

        $last = $timer
            ->measure('test1')
            ->measure('test2')
            ->measure('test3')
            ->last();

        self::assertMatchesRegularExpression('~test3: \d{1,2}ms~', $last->colon());
    }

    public function testStart() : void
    {
        $time = hrtime(true);

        $timer = new NanoTimerMock($time);

        self::assertSame($time, $timer->start());
    }

    public function testReset() : void
    {
        $time = hrtime(true);

        $timer = new NanoTimerMock($time);
        $timer->measure('test1');

        usleep(20);

        $timer
            ->reset(true)
            ->measure('test2');

        $delta = round((hrtime(true) - $time) / 1000000, 0, PHP_ROUND_HALF_UP);
        self::assertSame("total: {$delta}ms - test2: {$delta}ms", $timer->line());

        $timer
            ->reset(false)
            ->measure('test3');

        $delta = $timer->last()->value();
        self::assertSame("total: {$delta}ms - test3: {$delta}ms", $timer->line());
    }
}

class NanoTimerMock extends NanoTimer
{
    public function __construct(?int $hrtime = null)
    {
        parent::__construct($hrtime);
    }

    protected function errorLog(string $message) : void
    {
        echo $message;
    }
}
