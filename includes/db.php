<?php
// includes/db.php - PDO wrapper and DB initialization for PIXMORA AI

require_once __DIR__ . '/../config.php';

function get_pdo() {
    static $pdo = null;
    if ($pdo) return $pdo;

    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }

    $cfg = pixmora_cfg();
    $db = $cfg['db'] ?? ['type' => 'sqlite', 'sqlite_path' => __DIR__ . '/../data/database.sqlite'];

    if ($db['type'] === 'sqlite') {
        $path = $db['sqlite_path'] ?? DATA_DIR . '/database.sqlite';
        $dsn = 'sqlite:' . $path;
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        $host = $db['mysql_host'] ?? '127.0.0.1';
        $port = $db['mysql_port'] ?? '3306';
        $name = $db['mysql_db'] ?? '';
        $user = $db['mysql_user'] ?? '';
        $pass = $db['mysql_pass'] ?? '';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    return $pdo;
}
