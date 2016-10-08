<?php

namespace Fervo\AdvisoryLocker;

trait PerformTrait
{
    abstract public function acquire(string $name);
    abstract public function release(string $name);

    public function performLocked(string $name, callable $callable)
    {
        $this->acquire($name);
        try {
            $callable();
        } finally {
            $this->release($name);
        }
    }

    public function performSpinlocked(string $name, callable $callable, int $waitMillis = 100, int $retries = 5)
    {
        $waitMicros = $waitMillis * 1000;

        do {
            try {
                $this->performLocked($name, $callable);
                return;
            } catch (Exception\AcquireFailedException $e) {
                $retries--;
                usleep($waitMicros);
            }
        } while ($retries >= 0);

        throw new Exception\AcquireFailedException();
    }
}
