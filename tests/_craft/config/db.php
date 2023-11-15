<?php

return [
    'server' => getenv('DB_SERVER') ?? '127.0.0.1',
    'database' => getenv('DB_NAME') ?? 'scout_testing',
    'user' => getenv('DB_USER') ?? 'root',
    'password' => getenv('DB_PASSWORD') ?? 'root',
    'schema' => getenv('DB_SCHEMA'),
    'tablePrefix' => '',
    'driver' => 'mysql',
    'port' => getenv('DB_PORT') ?? 3306,
];
