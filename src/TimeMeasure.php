<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class TimeMeasure extends AbstractMeasure
{
    private readonly int $delta;

    public function __construct(string $label, int $delta)
    {
        $this->label = $label;
        $this->delta = $delta;
    }

    public function delta() : int
    {
        return $this->delta;
    }

    public function value() : int
    {
        return (int) round($this->delta / 1000000, 0, PHP_ROUND_HALF_UP);
    }

    public function valueStr() : string
    {
        return $this->value() . 'ms';
    }
}
