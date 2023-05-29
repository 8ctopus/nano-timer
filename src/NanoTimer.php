<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoTimer
{
    private bool $logMemoryPeakUse;
    private ?int $logSlowerThan;
    private bool $autoLog;

    private array $timings;

    /**
     * Constructor
     *
     * @param ?float $hrtime
     */
    public function __construct(?float $hrtime = null)
    {
        $this->logMemoryPeakUse = false;
        $this->logSlowerThan = null;
        $this->autoLog = false;

        if ($hrtime) {
            $this->timings[] = ['start', $hrtime];
        } else {
            $this->measure('start');
        }
    }

    public function __destruct()
    {
        if ($this->autoLog) {
            $this->measure('destruct');

            $report = $this->report(true);

            if ($report) {
                error_log($report);
            }
        }
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
        $this->timings[] = [$label, hrtime(true)];
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
     * @param  int    $milliseconds
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
     * @return self
     */
    public function autoLog() : self
    {
        $this->autoLog = true;
        return $this;
    }

    /**
     * Get report
     *
     * @param bool $table
     *
     * @return ?string
     */
    public function report(bool $table) : ?string
    {
        $i = 0;
        $first = 0;
        $last = 0;
        $data = [];

        foreach ($this->timings as $timing) {
            $label = $timing[0];
            $time = $timing[1];

            if ($i++ === 0) {
                $first = $time;
                $last = $time;
                continue;
            }

            $current = round(($time - $last) / 1000000, 0, PHP_ROUND_HALF_UP);

            $data[] = [$label => "{$current}ms"];

            $last = $time;
        }

        $total = round(($time - $first) / 1000000, 0, PHP_ROUND_HALF_UP);

        if ($this->logSlowerThan && $total < $this->logSlowerThan) {
            return null;
        }

        $data[] = ['total' => "{$total}ms"];

        if ($this->logMemoryPeakUse) {
            $used = memory_get_peak_usage(true);
            $used = round($used / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

            $data[] = ['memory peak use' => "{$used}MB"];
        }

        if ($table) {
            return $this->table($data);
        } else {
            return $this->singleLine($data);
        }
    }

    public function __toString() : string
    {
        return $this->report(true);
    }

    protected function table(array $data) : string
    {
        $max = 0;

        foreach ($data as $row) {
            $key = key($row);
            $max = max($max, strlen($key));
        }

        $table = '';

        foreach ($data as $row) {
            $key = key($row);
            $value = $row[$key];

            $table .= str_pad($key, $max + 1, ' ', STR_PAD_RIGHT) . str_pad($value, 6, ' ', STR_PAD_LEFT) . PHP_EOL;
        }

        return $table;
    }

    protected function singleLine(array $data) : string
    {
        $line = '';

        foreach ($data as $row) {
            $key = key($row);
            $value = $row[$key];

            $line .= "{$key}: {$value} - ";
        }

        return rtrim($line, ' - ');
    }
}
