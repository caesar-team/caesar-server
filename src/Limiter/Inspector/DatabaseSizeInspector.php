<?php

declare(strict_types=1);

namespace App\Limiter\Inspector;

use Doctrine\DBAL\Connection;

final class DatabaseSizeInspector extends AbstractInspector implements InspectorInterface
{
    // in bytes
    private const DEFAULT_RESERVED_SPACE = 10000000;

    private Connection $connection;
    private string $databaseName;

    public function __construct(Connection $connection, string $databaseName)
    {
        $this->connection = $connection;
        $this->databaseName = $databaseName;
    }

    public function getUsed(int $addedSize = 0): int
    {
        $result = $this->connection->executeQuery('SELECT pg_database_size(?);', [$this->databaseName]);

        $size = (int) $result->fetch()['pg_database_size'];

        return $size - self::DEFAULT_RESERVED_SPACE + $addedSize;
    }

    public function getErrorMessage(): string
    {
        return 'limiter.exception.database_size';
    }
}
