<?php

declare(strict_types=1);

namespace Oct8pus\NanoTimer;

class NanoTimer
{
    private array $timings;
    private ?int $logSlowerThan;

    /**
     * Constructor
     *
     * @param ?int $logSlowerThan
     * @param ?float $hrtime
     */
    public function __construct(?float $hrtime, ?int $logSlowerThan)
    {
        if ($hrtime) {
            $this->timings[] = ['start', $hrtime];
        } else {
            $this->measure('start');
        }

        $this->logSlowerThan = $logSlowerThan;
    }

    /**
     * Measure
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
     * Log elapsed time
     *
     * @param bool $memoryUse
     *
     * @return string
     */
    public function log(bool $memoryUse) : string
    {
        $message = '';
        $i = 0;
        $first = 0;
        $last = 0;

        foreach ($this->timings as $timing) {
            $label = $timing[0];
            $time = $timing[1];

            if ($i++ === 0) {
                $first = $time;
                $last = $time;
                continue;
            }

            $current = round(($time - $last) / 1000000, 0, PHP_ROUND_HALF_UP);

            $message .= "{$label} {$current}ms - ";

            $last = $time;
        }

        $total = round(($time - $first) / 1000000, 0, PHP_ROUND_HALF_UP);

        if ($this->logSlowerThan && $total < $this->logSlowerThan) {
            return '';
        }

        $message .= "total {$total}ms";

        if ($memoryUse) {
            $used = memory_get_peak_usage(true);
            $used = round($used / (1024 * 1024), 1, PHP_ROUND_HALF_UP);

            $message .= " - peak use {$used}MB";
        }

        return $message;
    }

    public function __toString() : string
    {
        return $this->log(true);
    }
}
