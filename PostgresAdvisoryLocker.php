<?php

namespace Fervo\AdvisoryLocker;

use Doctrine\DBAL\Connection;

/**
*
*/
class PostgresAdvisoryLocker implements AdvisoryLockerInterface
{
    use PerformTrait;

    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function acquire(string $name)
    {
        $key = $this->createKey($name);
        $rs = $this->conn->query("SELECT pg_try_advisory_lock($key);");

        if ($rs->fetchColumn(0) === false) {
            throw new Exception\AcquireFailedException();
        }
    }

    public function release(string $name)
    {
        $key = $this->createKey($name);
        $rs = $this->conn->query("SELECT pg_advisory_unlock($key);");

        if ($rs->fetchColumn(0) === false) {
            throw new Exception\ReleaseFailedException();
        }
    }

    protected function createKey(string $name): int
    {
        return crc32($name);
    }
}
