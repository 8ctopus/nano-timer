<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoTimer
{
    private bool $logMemoryPeakUse;
    private ?int $logSlowerThan;
    private bool $autoLog;
    private string $label;

    /**
     * @var Measures[]
     */
    private array $measures;

    /**
     * Constructor
     *
     * @param ?int $hrtime
     */
    public function __construct(?int $hrtime = null)
    {
        $this->logMemoryPeakUse = false;
        $this->logSlowerThan = null;
        $this->autoLog = false;
        $this->label = 'nanotimer';

        $this->measures = [];
        $this->measures[] = new TimeMeasure('start', $hrtime);
    }

    public function __destruct()
    {
        if (!$this->autoLog) {
            return;
        }

        $this
            ->measure('destruct')
            ->log();
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
     * Make measurement
     *
     * @param string $label
     *
     * @return self
     */
    public function measure(string $label) : self
    {
        $this->measures[] = new TimeMeasure($label);

        return $this;
    }

    /**
     * Log peak memory use
     *
     * @return self
     */
    public function logMemoryPeakUse() : self
    {
        $this->logMemoryPeakUse = true;
        return $this;
    }

    /**
     * Log only if total time more than
     *
     * @param int $milliseconds
     *
     * @return self
     */
    public function logSlowerThan(int $milliseconds) : self
    {
        $this->logSlowerThan = $milliseconds;
        return $this;
    }

    /**
     * Automatically logs when destructor is called
     *
     * @param bool $autoLog
     *
     * @return self
     */
    public function autoLog(bool $autoLog = true) : self
    {
        $this->autoLog = $autoLog;
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

        $max = 0;

        // get data max character length
        foreach ($data as $row) {
            $max = max($max, strlen($row->label()));
        }

        // create table
        $table = '';

        foreach ($data as $row) {
            $table .= str_pad($row->label(), $max + 1, ' ', STR_PAD_RIGHT) . str_pad($row->str(), 6, ' ', STR_PAD_LEFT) . "\n";
        }

        return $table;
    }

    /**
     * Single line report
     *
     * @return string
     */
    public function line() : string
    {
        $data = $this->data();

        if ($data === null) {
            return '';
        }

        // move total to first position
        $index = count($data) - 1;

        if ($this->logMemoryPeakUse) {
            --$index;
        }

        array_unshift($data, $data[$index]);
        unset($data[$index + 1]);

        $line = '';

        foreach ($data as $row) {
            $label = $row->label();

            if ($row instanceof TimeMeasure) {
                $value = $row->time() . 'ms';
            } else {
                $value = $row->memory() . 'MB';
            }

            $line .= "{$label}: {$value} - ";
        }

        return rtrim($line, ' - ');
    }

    /**
     * Set timer label
     *
     * @param string $label
     */
    public function setLabel(string $label) : self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Log to error
     *
     * @return self
     */
    public function log() : self
    {
        $log = $this->line();

        if (!empty($log)) {
            $this->errorLog("{$this->label} - {$log}");
        }

        return $this;
    }

    /**
     * Get report data
     *
     * @return ?Measures[]
     */
    public function data() : ?array
    {
        $index = 0;
        $first = 0;
        $last = 0;
        $data = [];

        foreach ($this->measures as $row) {
            $time = $row->hrtime();

            if ($index++ === 0) {
                $first = $time;
                $last = $time;
                continue;
            }

            $data[] = new TimeMeasure($row->label(), $time - $last);

            $last = $time;
        }

        if ($index === 0) {
            return null;
        }

        $total = $time - $first;

        $data[] = new TimeMeasure('total', $total);

        if ($this->logSlowerThan && round(($total) / 1000000, 0, PHP_ROUND_HALF_UP) < $this->logSlowerThan) {
            return null;
        }

        if ($this->logMemoryPeakUse) {
            $data[] = new MemoryMeasure('memory peak use');
        }

        return $data;
    }

    /**
     * Get start time
     *
     * @return int
     */
    public function startTime() : int
    {
        return (int) $this->measures[0]->hrtime();
    }

    /**
     * Get last measurement
     *
     * @return string
     */
    public function last() : string
    {
        $count = count($this->measures);

        $last = $this->measures[$count - 1];
        $before = $this->measures[$count - 2];

        $delta = $last->hrtime() - $before->hrtime();

        $delta = round($delta / 1000000, 0, PHP_ROUND_HALF_UP);

        return $last->label() . ": {$delta}ms";
    }

    /**
     * Get total
     *
     * @return string
     */
    public function total() : string
    {
        $data = $this->data();

        $count = count($data);

        if ($this->logMemoryPeakUse) {
            $count -1;
        }

        return $data[$count - 1]->str();
    }

    /**
     * Error log function that can be overriden in tests
     *
     * @param string $message
     *
     * @return void
     */
    protected function errorLog(string $message) : void
    {
        // @codeCoverageIgnoreStart
        error_log($message);
        // @codeCoverageIgnoreEnd
    }
}
