<?php

return [
    'server' => getenv('DB_SERVER') ?? 'db',
    'database' => getenv('DB_NAME') ?? 'scout_testing',
    'user' => getenv('DB_USER') ?? 'db',
    'password' => getenv('DB_PASSWORD') ?? 'db',
    'schema' => getenv('DB_SCHEMA'),
    'tablePrefix' => '',
    'driver' => 'mysql',
    'port' => getenv('DB_PORT') ?? 3306,
];
