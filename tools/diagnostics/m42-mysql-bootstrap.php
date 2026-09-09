<?php

// Only the owning runner uses this bootstrap; credentials never enter argv/output.
function required(string $key): string
{
    $value = getenv('M42_'.$key);
    if (! is_string($value) || $value === '') {
        throw new RuntimeException('Missing bootstrap field: '.$key);
    }

    return $value;
}

try {
    $dir = realpath(required('DATADIR'));
    $root = realpath(dirname(__DIR__, 2).'/storage/app/seo-m4-2-local');
    $name = required('DATABASE');
    $user = required('USER');
    if (required('OPT_IN') !== 'disposable-local-mysql' || ! $root || ! $dir
        || ! str_starts_with($dir, $root.DIRECTORY_SEPARATOR)
        || ! preg_match('/^m42[a-f0-9]{32}$/D', $name)
        || ! preg_match('/^u[a-f0-9]{16}$/D', $user)) {
        throw new RuntimeException('Invalid disposable bootstrap ownership.');
    }
    $pdo = new PDO('mysql:host=127.0.0.1;port='.required('PORT').';charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $verify = static function (?string $database) use ($pdo, $dir): void {
        $row = $pdo->query('SELECT DATABASE() db, CURRENT_USER() usr, @@datadir datadir, @@server_uuid uuid, @@port port, VERSION() version')->fetch(PDO::FETCH_ASSOC);
        if ($row['db'] !== $database || $row['usr'] !== 'root@localhost'
            || realpath($row['datadir']) !== $dir || $row['uuid'] !== required('SERVER_UUID')
            || (string) $row['port'] !== required('PORT') || str_contains($row['version'], 'MariaDB')) {
            throw new RuntimeException('Actual bootstrap connection is not owned by this run.');
        }
    };
    $verify(null);
    $pdo->exec('ALTER USER CURRENT_USER() IDENTIFIED BY '.$pdo->quote(required('ROOT_PASSWORD')));
    $verify(null);
    $pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `'.$name.'`');
    $verify($name);
    $pdo->exec('CREATE TABLE m42_ownership (run_id varchar(32) PRIMARY KEY)');
    $verify($name);
    $pdo->prepare('INSERT INTO m42_ownership (run_id) VALUES (?)')->execute([required('RUN_ID')]);
    $verify($name);
    $pdo->exec('CREATE USER '.$pdo->quote($user).'@\'127.0.0.1\' IDENTIFIED BY '.$pdo->quote(required('PASSWORD')));
    $verify($name);
    $pdo->exec('GRANT ALL ON `'.$name.'`.* TO '.$pdo->quote($user).'@\'127.0.0.1\'');
    echo json_encode(['bootstrap' => 'owned instance, unique database, database-only user verified']);
} catch (Throwable $e) {
    // SQL errors can echo password literals. Never print the exception message/SQL.
    fwrite(STDERR, json_encode(['bootstrap_error_class' => get_class($e), 'code' => $e->getCode()]));
    exit(2);
}
