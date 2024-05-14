<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoVariability
{
    private float $start;
    private float $last;

    /**
     * @var TimeMeasure[]
     */
    private array $measures;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->measures = [];

        $this->start = hrtime(true);
        $this->last = $this->start;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->table();
    }

    /**
     * Take measurement
     *
     * @param string $label
     *
     * @return self
     */
    public function measure(string $label) : self
    {
        $time = hrtime(true);

        $this->measures[] = new TimeMeasure($label, $time - $this->last);
        $this->last = $time;

        return $this;
    }

    /**
     * Table report
     *
     * @return string
     */
    public function table() : string
    {
        $data = $this->data();

        if ($data === null) {
            return '';
        }

        $maxLabel = 0;
        $maxValue = 0;

        // get label max length
        foreach ($data as $row) {
            $maxLabel = max($maxLabel, strlen($row->label()));
            $maxValue = max($maxValue, strlen($row->value()));
        }

        ++$maxLabel;

        // create table
        $table = '';

        foreach ($data as $row) {
            $table .= $row->pad($maxLabel, $maxValue) . "\n";
        }

        return $table;
    }

    /**
     * Get report data
     *
     * @return ?AbstractMeasure[]
     */
    public function data() : ?array
    {
        $measures = $this->measures;

        $min = PHP_INT_MAX;
        $max = 0;
        $values = [];

        foreach ($measures as $measure) {
            $values[] = $measure->hrtime();
            $min = min($min, $measure->hrtime());
            $max = max($max, $measure->hrtime());
        }

        sort($values);
        $count = count($measures);

        if ($count % 2 === 1) {
            $median = $values[ceil($count / 2) - 1];
        } else {
            $median = ($values[$count / 2 - 1] + $values[$count / 2]) / 2;
        }

        $measures[] = new TimeMeasure('average', array_sum($values) / $count);
        $measures[] = new TimeMeasure('median', $median);
        $measures[] = new TimeMeasure('minimum', $min);
        $measures[] = new TimeMeasure('maximum', $max);

        return $measures;
    }

    /**
     * Get last measurement
     *
     * @return TimeMeasure
     */
    public function last() : AbstractMeasure
    {
        $count = count($this->measures);

        return $this->measures[$count - 1];
    }
}
