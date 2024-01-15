<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoTimer
{
    private bool $logMemoryPeakUse;
    private ?int $logSlowerThan;
    private bool $autoLog;
    private string $label;
    private ?float $start;

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
        $this->start = $hrtime ?? hrtime(true);
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
     * Make measurement
     *
     * @param string $label
     *
     * @return self
     */
    public function measure(string $label) : self
    {
        $this->measures[] = new TimeMeasure($label, hrtime(true) - $this->start);

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

        $max += 1;

        // create table
        $table = '';

        foreach ($data as $row) {
            $table .= $row->pad($max);
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
     * To string
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->table();
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
        $data = $this->measures;

        $total = hrtime(true) - $this->start;

        $data[] = new TimeMeasure('total', $total);

        if ($this->logSlowerThan && $total < $this->logSlowerThan * 1000000) {
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
        return (int) $this->start;
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
