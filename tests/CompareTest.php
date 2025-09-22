<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\NanoTimer\Compare;
use Oct8pus\NanoTimer\NanoTimer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Compare::class)]
final class CompareTest extends TestCase
{
    public function test() : void
    {
        $t1 = new NanoTimer();

        $compare = new Compare($t1, clone $t1);

        self::assertSame('', $compare->table());

        $t1->measure('test');
        $compare = new Compare($t1, clone $t1);

        $delta = $t1->last()->value();

        $output = <<<OUTPUT
        total  {$delta} {$delta} -

        OUTPUT;

        self::assertSame($output, $compare->table());
    }
}
