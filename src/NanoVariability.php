<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoVariability extends AbstractMeasures
{
    private readonly int $start;
    private int $last;

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
     * @param bool $includeData
     *
     * @return string
     */
    public function table(bool $includeData = true) : string
    {
        $data = $this->data($includeData);

        if ($data === null) {
            return '';
        }

        $maxLabel = 0;
        $maxValue = 0;

        // get label max length
        foreach ($data as $row) {
            $maxLabel = max($maxLabel, strlen($row->label()));
            $maxValue = max($maxValue, strlen($row->valueStr()));
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
     * @param bool $includeData
     *
     * @return ?AbstractMeasure[]
     */
    public function data(bool $includeData = true) : ?array
    {
        $measures = $this->measures;
        $count = count($measures);

        if ($count === 0) {
            return null;
        }

        $min = PHP_INT_MAX;
        $max = 0;
        $values = [];

        foreach ($measures as $measure) {
            $values[] = $measure->delta();
            $min = min($min, $measure->delta());
            $max = max($max, $measure->delta());
        }

        sort($values);

        if ($count % 2 === 1) {
            $median = $values[floor($count / 2)];
        } else {
            $middle = $count / 2;
            $median = ($values[$middle - 1] + $values[$middle]) / 2;
        }

        if (!$includeData) {
            $measures = [];
        }

        $measures[] = new TimeMeasure('average', (int) round(array_sum($values) / $count, 0, PHP_ROUND_HALF_UP));
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
