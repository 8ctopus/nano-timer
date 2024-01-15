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

    public function value() : string
    {
        return (string) $this->memory() . 'MB';
    }

    public function colon() : string
    {
        return $this->label() . ': ' . $this->value();
    }

    public function pad(int $labelPad, int $valuePad) : string
    {
        return str_pad($this->label(), $labelPad, ' ', STR_PAD_RIGHT) .  str_pad($this->value(), $valuePad, ' ', STR_PAD_LEFT);
    }
}
