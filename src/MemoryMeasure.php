<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class MemoryMeasure implements Measure
{
    private readonly string $label;
    private readonly float $memory;

    public function __construct(string $label, ?float $memory = null)
    {
        $this->label = $label;
        $this->memory = $memory ?? memory_get_peak_usage(true);

    }

    public function label() : string
    {
        return $this->label;
    }

    public function memory() : float
    {
        return round($this->memory / (1024 * 1024), 1, PHP_ROUND_HALF_UP);
    }

    public function str() : string
    {
        return (string) $this->memory() . 'MB';
    }
}
