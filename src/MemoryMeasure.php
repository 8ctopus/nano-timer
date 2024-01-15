<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class MemoryMeasure extends AbstractMeasure
{
    private readonly float $memory;

    public function __construct(string $label)
    {
        $this->label = $label;
        $this->memory = memory_get_peak_usage(true);
    }

    public function memory() : float
    {
        return $this->memory;
    }

    public function value() : string
    {
        $memory = (string) round($this->memory / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

        return $memory . 'MB';
    }
}
