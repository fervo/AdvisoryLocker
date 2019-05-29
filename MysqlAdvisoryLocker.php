<?php

namespace Fervo\AdvisoryLocker;

use Doctrine\DBAL\Connection;

/**
*
*/
class MysqlAdvisoryLocker implements AdvisoryLockerInterface
{
    use PerformTrait;

    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function acquire(string $name)
    {
        $this->doAcquireLock($name, 0);
    }

    public function doAcquireLock(string $name, int $wait)
    {
        $quotedName = $this->lockName($name);
        $rs = $this->conn->query("SELECT GET_LOCK($quotedName, $wait);");

        if ($rs->fetchColumn(0) !== '1') {
            throw new Exception\AcquireFailedException();
        }
    }

    private function lockName(string $name)
    {
        $newName = $this->conn->getDatabase().'-'.$this->conn->quote($name, \PDO::PARAM_STR);

        if (strlen($newName) > 64) {
            $newName = crc32($newName);
        }

        return $newName;
    }

    public function release(string $name)
    {
        $quotedName = $this->lockName($name);
        $rs = $this->conn->query("SELECT RELEASE_LOCK($quotedName);");

        if ($rs->fetchColumn(0) !== '1') {
            throw new Exception\ReleaseFailedException();
        }
    }

    public function performSpinlocked(string $name, callable $callable, int $waitMillis = 100, int $retries = 5)
    {
        $totalWaitTime = round($waitMillis * $retries / 1000);

        $this->doAcquireLock($name, $totalWaitTime);

        try {
            $callable();
        } finally {
            $this->release($name);
        }
    }
}
