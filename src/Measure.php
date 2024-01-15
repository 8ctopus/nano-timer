<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

abstract class Measure
{
    public abstract function label() : string;
    public abstract function value() : string;

    public function colon() : string
    {
        return $this->label() . ': ' . $this->value();
    }

    public function pad(int $labelPad, int $valuePad) : string
    {
        return str_pad($this->label(), $labelPad, ' ', STR_PAD_RIGHT) .  str_pad($this->value(), $valuePad, ' ', STR_PAD_LEFT);
    }
}
