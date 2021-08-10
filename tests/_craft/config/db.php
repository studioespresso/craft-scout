<?php

return [
    'password'    => "",
    'user'        => "root",
    'database'    => "scout_testing",
    'tablePrefix' => getenv('DB_TABLE_PREFIX'),
    'driver'      => "mysql",
    'port'        => "3306",
    'schema'      => getenv('DB_SCHEMA'),
    'server'      => getenv('DB_SERVER'),
];
