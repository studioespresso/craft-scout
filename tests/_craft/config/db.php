<?php

return [
    'server' => getenv('DB_SERVER') ?? '127.0.0.1',
    'user' => getenv('DB_USER') ?? 'root',
    'password' => getenv('DB_PASSWORD') ?? 'root',
    'database' => getenv('DB_DATABASE') ?? 'scout_testing',
    'schema' => getenv('DB_SCHEMA'),
    'tablePrefix' => '',
    'driver' => 'mysql',
    'port' => getenv('DB_PORT') ?? 3306,
];
