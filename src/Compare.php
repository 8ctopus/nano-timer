<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class Compare
{
    private readonly NanoVariability $v1;
    private readonly NanoVariability $v2;

    public function __construct(NanoVariability $v1, NanoVariability $v2)
    {
        $this->v1 = $v1;
        $this->v2 = $v2;
    }

    public function table(bool $includeData = false) : string
    {
        $data1 = $this->v1->data($includeData);
        $data2 = $this->v2->data($includeData);

        $count = count($data1);

        if (!$data1 || !$data2 || $count !== count($data2)) {
            return '';
        }

        $maxLabel = 0;
        $maxValue = 0;

        // get max length
        for ($i = 0; $i < $count; ++$i) {
            $maxLabel = max($maxLabel, strlen($data1[$i]->label()));
            $maxValue = max($maxValue, strlen((string) $data1[$i]->value()), strlen((string) $data2[$i]->value()));
        }

        ++$maxLabel;
        ++$maxValue;

        // create table
        $table = '';

        for ($i = 0; $i < $count; ++$i) {
            $value1 = $data1[$i]->value();
            $value2 = $data2[$i]->value();

            $table .= $data1[$i]->label($maxLabel) . $this->pad($value1, $maxValue) .  $this->pad($value2, $maxValue) . sprintf(" %+.0f", $this->percentage($value1, $value2)) . "%\n";
        }

        return $table;
    }

    private function percentage(int $value1, int $value2) : float
    {
        return 100 * ($value2 - $value1) / $value1;
    }

    private function pad(int $value, int $pad) : string
    {
        return str_pad((string) $value, $pad, ' ', STR_PAD_LEFT);
    }
}
