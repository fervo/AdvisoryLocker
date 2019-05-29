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
    protected $databaseScope;

    public function __construct(Connection $conn, bool $databaseScope = false)
    {
        $this->conn = $conn;
        $this->databaseScope = $databaseScope;
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
        if ($this->databaseScope) {
            $fullName = $this->conn->getDatabase().'-'.$name;
        } else {
            $fullName = $name;
        }

        return crc32($fullName);
    }
}
