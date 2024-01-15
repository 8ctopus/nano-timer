<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

abstract class AbstractMeasure
{
    protected string $label;

    public function label() : string
    {
        return $this->label;
    }

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
