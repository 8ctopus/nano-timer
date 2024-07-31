<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoTimer extends AbstractMeasures
{
    private bool $logMemoryPeakUse;
    private ?int $logSlowerThan;
    private bool $autoLog;
    private string $label;
    private float $start;
    private float $last;

    /**
     * @var TimeMeasure[]
     */
    private array $measures;

    /**
     * Constructor
     *
     * @param ?int $hrtime
     */
    public function __construct(?int $hrtime = null)
    {
        $this->start = $hrtime ?? hrtime(true);

        $this->last = $this->start;
        $this->measures = [];
        $this->logMemoryPeakUse = false;
        $this->logSlowerThan = null;
        $this->autoLog = false;
        $this->label = 'nanotimer';
    }

    /**
     * Destructor
     */
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
            $line .= $row->colon() . ' - ';
        }

        return rtrim($line, ' - ');
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
        $total = hrtime(true) - $this->start;

        if (isset($this->logSlowerThan) && $total < $this->logSlowerThan * 1000000) {
            return null;
        }

        $measures = $includeData ? $this->measures : [];

        $measures[] = new TimeMeasure('total', $total);

        if ($this->logMemoryPeakUse) {
            $measures[] = new MemoryMeasure('memory peak use');
        }

        return $measures;
    }

    /**
     * Get total
     *
     * @return TimeMeasure
     */
    public function total() : TimeMeasure
    {
        return new TimeMeasure('total', hrtime(true) - $this->start);
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

    /**
     * Get start time
     *
     * @return float
     */
    public function start() : float
    {
        return $this->start;
    }

    /**
     * Reset all measurements
     *
     * @param bool $keepStart
     *
     * @return self
     */
    public function reset(bool $keepStart = true) : self
    {
        $this->measures = [];

        if ($keepStart === false) {
            $this->start = hrtime(true);
        }

        return $this;
    }

    /**
     * Log peak memory use
     *
     * @param bool $value
     *
     * @return self
     */
    public function logMemoryPeakUse(bool $value = true) : self
    {
        $this->logMemoryPeakUse = $value;
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
