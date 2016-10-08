<?php

namespace Fervo\AdvisoryLocker;

interface AdvisoryLockerInterface
{
    public function performLocked(string $name, callable $callable);
    public function performSpinlocked(string $name, callable $callable, int $waitMillis = 100, int $retries = 5);

    public function acquire(string $name);
    public function release(string $name);
}
