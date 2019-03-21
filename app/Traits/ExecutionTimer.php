<?php

namespace App\Traits;

trait ExecutionTimer
{
    private $start;
    private $executionTime;

    /**
     * Start timer
     */
    protected function executionStart()
    {
        $this->start = microtime(true);
    }

    /**
     * Calculate time
     */
    protected function executionEnd()
    {
        $this->executionTime = microtime(true) - $this->start;
    }

    /**
     * Display formatted script's execution time
     * @return string
     */
    protected function executionTimeForHuman()
    {
        $minutes = floor($this->executionTime / 60);
        $seconds = $this->executionTime - ($minutes * 60);
        $seconds = $seconds < 1 ? round($seconds, 1) : round($seconds);

        $minutesString = $minutes !== 1 ? 'minutes' : 'minute';
        $secondsString = $seconds !== 1 ? 'seconds' : 'second';
        return "{$minutes} {$minutesString} and {$seconds} {$secondsString}";
    }
}