<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

interface Measure
{
    public function label() : string;
    public function str() : string;
    public function pad(int $padding, bool $dot) : string;
}
