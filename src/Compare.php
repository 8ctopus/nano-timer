<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class Compare
{
    private readonly AbstractMeasures $measures1;
    private readonly AbstractMeasures $measures2;

    public function __construct(AbstractMeasures $measures1, AbstractMeasures $measures2)
    {
        $this->measures1 = $measures1;
        $this->measures2 = $measures2;
    }

    public function table(bool $includeData = false) : string
    {
        $data1 = $this->measures1->data($includeData);
        $data2 = $this->measures2->data($includeData);

        $count = count($data1 ?? []);

        if (!$data1 || !$data2 || !$count || $count !== count($data2)) {
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

            $percentage = sprintf('%+.0f%%', $this->percentage($value1, $value2));

            if ($percentage === '+0%') {
                $percentage = '-';
            }

            $table .= $data1[$i]->label($maxLabel) . $this->pad($value1, $maxValue) . $this->pad($value2, $maxValue) . " {$percentage}\n";
        }

        return $table;
    }

    private function percentage(int $value1, int $value2) : float
    {
        if ($value1 === 0) {
            return 0;
        }

        return 100 * ($value2 - $value1) / $value1;
    }

    private function pad(int $value, int $pad) : string
    {
        return str_pad((string) $value, $pad, ' ', STR_PAD_LEFT);
    }
}
