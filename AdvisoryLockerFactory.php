<?php

namespace Fervo\AdvisoryLocker;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;

/**
*
*/
class AdvisoryLockerFactory
{
    public static function createLocker(Connection $conn): AdvisoryLockerInterface
    {
        $platform = $conn->getDatabasePlatform();

        switch (true) {
            case $platform instanceof PostgreSqlPlatform:
                return new PostgresAdvisoryLocker($conn);
            case $platform instanceof MySQLPlatform:
                return new MysqlAdvisoryLocker($conn);
            default:
                throw new Exception\PlatformNotSupportedException();
        }
    }
}
