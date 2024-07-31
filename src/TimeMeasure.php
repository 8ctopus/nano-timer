<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class TimeMeasure extends AbstractMeasure
{
    private readonly int $hrtime;

    public function __construct(string $label, int $hrtime)
    {
        $this->label = $label;
        $this->hrtime = $hrtime;
    }

    public function hrtime() : int
    {
        return $this->hrtime;
    }

    public function value() : int
    {
        return (int) round($this->hrtime / 1000000, 0, PHP_ROUND_HALF_UP);
    }

    public function valueStr() : string
    {
        return $this->value() . 'ms';
    }
}
