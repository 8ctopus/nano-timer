<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class TimeMeasure extends AbstractMeasure
{
    private readonly float $hrtime;

    public function __construct(string $label, float $hrtime)
    {
        $this->label = $label;
        $this->hrtime = $hrtime;
    }

    public function hrtime() : float
    {
        return $this->hrtime;
    }

    public function ms() : float
    {
        return round($this->hrtime / 1000000, 0, PHP_ROUND_HALF_UP);
    }

    public function value() : string
    {
        return (string) $this->ms() . 'ms';
    }
}
