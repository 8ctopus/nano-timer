<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

abstract class AbstractMeasure
{
    protected string $label;

    public function label(int $pad = 0) : string
    {
        return str_pad($this->label, $pad, ' ', STR_PAD_RIGHT);
    }

    abstract public function value() : int;

    abstract public function valueStr() : string;

    public function colon() : string
    {
        return $this->label() . ': ' . $this->value();
    }

    public function pad(int $labelPad, int $valuePad) : string
    {
        return $this->label($labelPad) . str_pad($this->valueStr(), $valuePad, ' ', STR_PAD_LEFT);
    }
}
