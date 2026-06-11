<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$envPath = $projectRoot.DIRECTORY_SEPARATOR.'.env';
$sqlPath = $projectRoot.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'lapmas_fresh_mysql.sql';
$cliDatabase = $argv[1] ?? null;

if (! extension_loaded('mysqli')) {
    fwrite(STDERR, "The mysqli extension is required to import the MySQL SQL file.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_OFF);

if (! is_file($envPath)) {
    fwrite(STDERR, ".env was not found.\n");
    exit(1);
}

if (! is_file($sqlPath)) {
    fwrite(STDERR, "SQL file was not found at {$sqlPath}.\n");
    exit(1);
}

$env = parseEnvFile($envPath);

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($env['DB_PORT'] ?? 3306);
$database = $cliDatabase ?: ($env['DB_DATABASE'] ?? null);
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

if ($database === null || trim($database) === '') {
    fwrite(STDERR, "DB_DATABASE is empty in .env.\n");
    exit(1);
}

$rootConnection = mysqli_init();

if ($rootConnection === false) {
    fwrite(STDERR, "Failed to initialize mysqli.\n");
    exit(1);
}

mysqli_options($rootConnection, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

if (! @mysqli_real_connect($rootConnection, $host, $username, $password, null, $port)) {
    fwrite(STDERR, sprintf(
        "Failed to connect to MySQL server %s:%d using database credentials from .env. Error: %s\n",
        $host,
        $port,
        mysqli_connect_error(),
    ));
    exit(1);
}

mysqli_set_charset($rootConnection, 'utf8mb4');

$escapedDatabase = '`'.str_replace('`', '``', $database).'`';

if (! mysqli_query($rootConnection, "CREATE DATABASE IF NOT EXISTS {$escapedDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    fwrite(STDERR, 'Failed to create database: '.mysqli_error($rootConnection)."\n");
    exit(1);
}

mysqli_close($rootConnection);

$connection = mysqli_init();

if ($connection === false) {
    fwrite(STDERR, "Failed to initialize mysqli.\n");
    exit(1);
}

mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

if (! @mysqli_real_connect($connection, $host, $username, $password, $database, $port)) {
    fwrite(STDERR, sprintf(
        "Failed to connect to target database %s. Error: %s\n",
        $database,
        mysqli_connect_error(),
    ));
    exit(1);
}

mysqli_set_charset($connection, 'utf8mb4');

$sql = file_get_contents($sqlPath);

if ($sql === false) {
    fwrite(STDERR, "Failed to read SQL file.\n");
    exit(1);
}

if (! mysqli_multi_query($connection, $sql)) {
    fwrite(STDERR, 'Failed to execute SQL import: '.mysqli_error($connection)."\n");
    exit(1);
}

while (mysqli_more_results($connection)) {
    if (! mysqli_next_result($connection)) {
        fwrite(STDERR, 'SQL import failed while advancing result set: '.mysqli_error($connection)."\n");
        exit(1);
    }

    $result = mysqli_store_result($connection);

    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }
}

mysqli_close($connection);

printf(
    "Imported %s into database %s on %s:%d successfully.\n",
    basename($sqlPath),
    $database,
    $host,
    $port,
);

function parseEnvFile(string $path): array
{
    $values = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        )) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}
