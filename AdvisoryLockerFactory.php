<?php

namespace Fervo\AdvisoryLocker;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
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
            case $platform instanceof MySqlPlatform:
                return new MysqlAdvisoryLocker($conn);
            default:
                throw new Exception\PlatformNotSupportedException();
        }
    }
}
