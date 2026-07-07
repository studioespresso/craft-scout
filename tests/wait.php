<?php

// Blocks until the MySQL container from tests/docker-compose.yml accepts
// connections, so the test suite doesn't start before the database is ready.

$host = getenv('DB_SERVER') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'scout_testing';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';

$start = time();

while (true) {
    try {
        new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $password);
        fwrite(STDOUT, 'Database is ready!' . PHP_EOL);
        exit(0);
    } catch (PDOException $exception) {
        if (time() - $start > 60) {
            fwrite(STDERR, 'Database did not become available in time: ' . $exception->getMessage() . PHP_EOL);
            exit(1);
        }
        fwrite(STDOUT, 'Waiting for the database to become available...' . PHP_EOL);
        sleep(1);
    }
}
