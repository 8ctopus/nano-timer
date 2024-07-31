<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

abstract class AbstractMeasures
{
    /**
     * To string
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->table();
    }

    abstract public function measure(string $label) : self;
    abstract public function table(bool $includeData = true) : string;
    abstract public function data(bool $includeData = true) : ?array;
    abstract public function last() : AbstractMeasure;
}
