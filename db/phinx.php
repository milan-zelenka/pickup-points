<?php

declare(strict_types=1);

$get = static fn (string $name): string
    => getenv($name) ?: throw new \RuntimeException("$name is not set");

return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'dev',
        'dev'  => [
            'adapter' => 'mysql',
            'host'    => $get('DB_HOST'),
            'name'    => $get('DB_NAME'),
            'user'    => $get('DB_USER'),
            'pass'    => $get('DB_PASS'),
            'charset' => 'utf8mb4',
        ],
    ],
];
