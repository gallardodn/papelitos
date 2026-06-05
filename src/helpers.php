<?php
declare(strict_types=1);

function db(): PDO
{
    $pdo = $GLOBALS['PAPELITOS_PDO'] ?? null;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('PDO no inicializado');
    }
    return $pdo;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 302);
    exit;
}

function flash_set(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_get(string $key, mixed $default = null): mixed
{
    if (!isset($_SESSION['_flash'][$key])) {
        return $default;
    }
    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    if (empty($_SESSION['_flash'])) {
        unset($_SESSION['_flash']);
    }
    return $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['_csrf'];
}

function csrf_verify(?string $token): bool
{
    if ($token === null || $token === '') {
        return false;
    }
    if (empty($_SESSION['_csrf'])) {
        return false;
    }
    return hash_equals((string)$_SESSION['_csrf'], $token);
}

function now_atom(): string
{
    return (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
}

function current_user_id(): ?int
{
    $id = $_SESSION['user_id'] ?? null;
    if ($id === null) {
        return null;
    }
    $id = filter_var($id, FILTER_VALIDATE_INT);
    return $id === false ? null : (int)$id;
}

function require_auth(): void
{
    if (current_user_id() === null) {
        redirect('/login');
    }
}

function render(string $view, array $params = []): void
{
    $viewFile = APP_ROOT . '/src/Views/' . ltrim($view, '/');
    if (!str_ends_with($viewFile, '.php')) {
        $viewFile .= '.php';
    }

    if (!is_file($viewFile)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Vista no encontrada';
        exit;
    }

    extract($params, EXTR_SKIP);

    ob_start();
    require $viewFile;
    $content = (string)ob_get_clean();

    $layout = APP_ROOT . '/src/Views/layout.php';
    require $layout;
}
