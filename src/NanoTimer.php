<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoTimer
{
    private bool $logMemoryPeakUse;
    private ?int $logSlowerThan;
    private bool $autoLog;

    /**
     * @var array<int, array<string, int>>
     */
    private array $timings;

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

        if ($hrtime) {
            $this->timings[] = ['start' => $hrtime];
        } else {
            $this->measure('start');
        }
    }

    public function __destruct()
    {
        if (!$this->autoLog) {
            return;
        }

        $this
            ->measure('destruct')
            ->errorLog($this->line());
    }

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
        $this->timings[] = [$label => hrtime(true)];
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
     * @return self
     */
    public function autoLog() : self
    {
        $this->autoLog = true;
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

        foreach ($data as $row) {
            $key = key($row);
            $max = max($max, strlen($key));
        }

        $table = '';

        foreach ($data as $row) {
            $key = key($row);
            $value = $row[$key];

            $table .= str_pad($key, $max + 1, ' ', STR_PAD_RIGHT) . str_pad($value, 6, ' ', STR_PAD_LEFT) . "\n";
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

        $line = '';

        foreach ($data as $row) {
            $key = key($row);
            $value = $row[$key];

            $line .= "{$key}: {$value} - ";
        }

        return rtrim($line, ' - ');
    }

    /**
     * Get report data
     *
     * @return ?array<int, array<string, string>>
     */
    protected function data() : ?array
    {
        $index = 0;
        $first = 0;
        $last = 0;
        $data = [];

        foreach ($this->timings as $row) {
            $label = key($row);
            $time = $row[$label];

            if ($index++ === 0) {
                $first = $time;
                $last = $time;
                continue;
            }

            $current = round(($time - $last) / 1000000, 0, PHP_ROUND_HALF_UP);

            $data[] = [$label => "{$current}ms"];

            $last = $time;
        }

        if ($index === 0) {
            return null;
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

        return $data;
    }

    protected function errorLog(string $log) : self
    {
        // @codeCoverageIgnoreStart
        if (!empty($log)) {
            error_log($log);
        }

        return $this;
        // @codeCoverageIgnoreEnd
    }
}
