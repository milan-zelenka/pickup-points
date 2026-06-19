<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

readonly class PdoConnectionFactory
{
    public function __construct(
        private string $dsn,
        private string $username = '',
        private string $password = '',
    ) {
    }

    public function create(): PDO
    {
        return new PDO($this->dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_czech_ci',
        ]);
    }
}
