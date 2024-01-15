<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class TimeMeasure implements Measure
{
    private readonly string $label;
    private readonly float $hrtime;

    public function __construct(string $label, float $hrtime)
    {
        $this->label = $label;
        $this->hrtime = $hrtime;
    }

    public function label() : string
    {
        return $this->label;
    }

    public function hrtime() : float
    {
        return $this->hrtime;
    }

    public function time() : float
    {
        return round($this->hrtime / 1000000, 0, PHP_ROUND_HALF_UP);
    }

    public function value() : string
    {
        return (string) $this->time() . 'ms';
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
