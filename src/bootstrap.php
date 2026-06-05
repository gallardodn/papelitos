<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('papelitos');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$dataDir = getenv('APP_DATA_DIR') ?: (APP_ROOT . '/data');
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}

define('APP_DATA_DIR', $dataDir);

$dbPath = rtrim(APP_DATA_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'app.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL
    )');

    $usersCols = $pdo->query("PRAGMA table_info('users')")?->fetchAll() ?: [];
    $hasEmail = false;
    $hasUsername = false;
    foreach ($usersCols as $c) {
        if (!is_array($c)) {
            continue;
        }
        $name = (string)($c['name'] ?? '');
        if ($name === 'email') {
            $hasEmail = true;
        } elseif ($name === 'username') {
            $hasUsername = true;
        }
    }

    if (!$hasEmail && $hasUsername) {
        $pdo->exec('PRAGMA foreign_keys = OFF');
        $pdo->beginTransaction();
        $pdo->exec('CREATE TABLE users_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL
        )');
        $pdo->exec('INSERT INTO users_new (id, email, password_hash, created_at)
            SELECT id, username, password_hash, created_at FROM users');
        $pdo->exec('DROP TABLE users');
        $pdo->exec('ALTER TABLE users_new RENAME TO users');
        $pdo->commit();
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS books (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        author TEXT NOT NULL,
        cover_path TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        book_id INTEGER NOT NULL,
        page_index INTEGER NOT NULL,
        drawing_path TEXT,
        image_path TEXT,
        text TEXT,
        text_position TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY(book_id) REFERENCES books(id) ON DELETE CASCADE
    )');

    $GLOBALS['PAPELITOS_PDO'] = $pdo;
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error inicializando la app';
    exit;
}

require_once APP_ROOT . '/src/helpers.php';

