<?php

return [
    'password'    => 'root',
    'user'        => 'root',
    'database'    => 'scout_testing',
    'tablePrefix' => '',
    'driver'      => "mysql",
    'port'        => 3306,
    'schema'      => getenv('DB_SCHEMA'),
    'server'      => '127.0.0.1',
];
