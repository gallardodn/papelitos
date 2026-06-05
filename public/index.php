<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');

function owned_book(int $bookId): ?array
{
    $stmt = db()->prepare('SELECT id, title, author FROM books WHERE id = :book_id AND user_id = :user_id LIMIT 1');
    $stmt->execute([
        ':book_id' => $bookId,
        ':user_id' => (int)current_user_id(),
    ]);
    $book = $stmt->fetch();
    return is_array($book) ? $book : null;
}

function ensure_cover_pages(int $bookId): array
{
    $stmt = db()->prepare('SELECT id, page_index, image_path, text, updated_at FROM pages WHERE book_id = :book_id AND page_index IN (-2, -1) ORDER BY page_index ASC');
    $stmt->execute([':book_id' => $bookId]);
    $rows = $stmt->fetchAll();

    $byIndex = [];
    foreach ($rows as $r) {
        if (is_array($r) && isset($r['page_index'])) {
            $byIndex[(int)$r['page_index']] = $r;
        }
    }

    $now = now_atom();
    $insert = db()->prepare('INSERT INTO pages (book_id, page_index, drawing_path, image_path, text, text_position, created_at, updated_at) VALUES (:book_id, :page_index, NULL, NULL, :text, :text_position, :created_at, :updated_at)');
    foreach ([-2, -1] as $idx) {
        if (!isset($byIndex[$idx])) {
            $insert->execute([
                ':book_id' => $bookId,
                ':page_index' => $idx,
                ':text' => '',
                ':text_position' => 'below',
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        }
    }

    $stmt = db()->prepare('SELECT id, page_index, image_path, text, updated_at FROM pages WHERE book_id = :book_id AND page_index IN (-2, -1) ORDER BY page_index ASC');
    $stmt->execute([':book_id' => $bookId]);
    $rows = $stmt->fetchAll();

    $cover = null;
    $back = null;
    foreach ($rows as $r) {
        if (!is_array($r)) {
            continue;
        }
        $pi = (int)($r['page_index'] ?? 0);
        if ($pi === -2) {
            $cover = $r;
        } elseif ($pi === -1) {
            $back = $r;
        }
    }

    if (!is_array($cover) || !is_array($back)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Error inicializando tapa/contratapa';
        exit;
    }

    return [$cover, $back];
}

function normalize_story_pages(int $bookId): array
{
    $stmt = db()->prepare('SELECT id, page_index, image_path, text, updated_at FROM pages WHERE book_id = :book_id AND page_index >= 0 ORDER BY page_index ASC, id ASC');
    $stmt->execute([':book_id' => $bookId]);
    $pages = $stmt->fetchAll();
    if (!is_array($pages)) {
        return [];
    }

    $updates = [];
    $i = 0;
    foreach ($pages as $p) {
        if (!is_array($p)) {
            continue;
        }
        $current = (int)($p['page_index'] ?? 0);
        if ($current !== $i) {
            $updates[] = ['id' => (int)$p['id'], 'page_index' => $i];
        }
        $i++;
    }

    if (count($updates) > 0) {
        db()->beginTransaction();
        $stmt = db()->prepare('UPDATE pages SET page_index = :page_index WHERE id = :id');
        foreach ($updates as $u) {
            $stmt->execute([
                ':page_index' => (int)$u['page_index'],
                ':id' => (int)$u['id'],
            ]);
        }
        db()->commit();

        $stmt = db()->prepare('SELECT id, page_index, image_path, text, updated_at FROM pages WHERE book_id = :book_id AND page_index >= 0 ORDER BY page_index ASC, id ASC');
        $stmt->execute([':book_id' => $bookId]);
        $pages = $stmt->fetchAll();
    }

    return is_array($pages) ? $pages : [];
}

function build_book_pages_model(int $bookId): array
{
    [$cover, $backCover] = ensure_cover_pages($bookId);
    $storyPages = normalize_story_pages($bookId);

    $sheets = [];
    $count = count($storyPages);
    $sheetCount = (int)ceil($count / 2);
    for ($s = 0; $s < $sheetCount; $s++) {
        $left = $storyPages[$s * 2] ?? null;
        $right = $storyPages[$s * 2 + 1] ?? null;
        if (!is_array($left)) {
            $left = null;
        }
        if (!is_array($right)) {
            $right = null;
        }
        $sheets[] = [
            'sheet_index' => $s,
            'left' => $left,
            'right' => $right,
        ];
    }

    return [
        'cover' => $cover,
        'backCover' => $backCover,
        'sheets' => $sheets,
    ];
}

if ($path === '/') {
    redirect(current_user_id() === null ? '/login' : '/menu');
}

if (str_starts_with($path, '/u/') && $method === 'GET') {
    $name = substr($path, 3);
    if (!is_string($name) || $name === '' || str_contains($name, '/') || str_contains($name, '\\')) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo '404';
        exit;
    }

    if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp|gif)$/', $name)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo '404';
        exit;
    }

    $filePath = rtrim(APP_DATA_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $name;
    if (!is_file($filePath)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo '404';
        exit;
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
    ];
    $contentType = $types[$ext] ?? 'application/octet-stream';

    header('Content-Type: ' . $contentType);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=86400');
    readfile($filePath);
    exit;
}

if ($path === '/login' && $method === 'GET') {
    render('auth/login', ['title' => 'Ingresar', 'username' => (string)flash_get('old_username', '')]);
    exit;
}

if ($path === '/login' && $method === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/login');
    }

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    flash_set('old_username', $username);

    if ($username === '' || $password === '') {
        flash_set('error', 'Completá usuario y contraseña.');
        redirect('/login');
    }

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch();

    if (!is_array($row) || empty($row['password_hash']) || !password_verify($password, (string)$row['password_hash'])) {
        flash_set('error', 'Usuario o contraseña incorrectos.');
        redirect('/login');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$row['id'];
    flash_set('success', '¡Bienvenido!');
    redirect('/menu');
}

if ($path === '/register' && $method === 'GET') {
    render('auth/register', ['title' => 'Crear cuenta', 'username' => (string)flash_get('old_username', '')]);
    exit;
}

if ($path === '/register' && $method === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/register');
    }

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    flash_set('old_username', $username);

    if ($username === '' || $password === '' || $passwordConfirm === '') {
        flash_set('error', 'Completá todos los campos.');
        redirect('/register');
    }

    if (!preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $username)) {
        flash_set('error', 'El usuario debe tener 3 a 32 caracteres (letras, números, punto, guion o guion bajo).');
        redirect('/register');
    }

    if (mb_strlen($password) < 6) {
        flash_set('error', 'La contraseña debe tener al menos 6 caracteres.');
        redirect('/register');
    }

    if ($password !== $passwordConfirm) {
        flash_set('error', 'Las contraseñas no coinciden.');
        redirect('/register');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $now = now_atom();

    try {
        $stmt = db()->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (:username, :hash, :created_at)');
        $stmt->execute([
            ':username' => $username,
            ':hash' => $hash,
            ':created_at' => $now,
        ]);
    } catch (PDOException $e) {
        flash_set('error', 'Ese usuario ya existe.');
        redirect('/register');
    }

    flash_set('success', 'Cuenta creada. Ahora podés ingresar.');
    redirect('/login');
}

if ($path === '/logout' && $method === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/menu');
    }
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
    flash_set('success', 'Sesión cerrada.');
    redirect('/login');
}

if ($path === '/menu' && $method === 'GET') {
    require_auth();
    render('menu', ['title' => 'Menú']);
    exit;
}

if ($path === '/new-book' && $method === 'GET') {
    require_auth();
    render('books/new', [
        'title' => 'Nuevo libro',
        'titleValue' => (string)flash_get('old_book_title', ''),
        'authorValue' => (string)flash_get('old_book_author', ''),
    ]);
    exit;
}

if ($path === '/new-book' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/new-book');
    }

    $title = trim((string)($_POST['title'] ?? ''));
    $author = trim((string)($_POST['author'] ?? ''));

    flash_set('old_book_title', $title);
    flash_set('old_book_author', $author);

    if ($title === '' || $author === '') {
        flash_set('error', 'Completá título y autor.');
        redirect('/new-book');
    }

    if (mb_strlen($title) > 80 || mb_strlen($author) > 80) {
        flash_set('error', 'Título y autor deben tener hasta 80 caracteres.');
        redirect('/new-book');
    }

    $now = now_atom();
    $stmt = db()->prepare('INSERT INTO books (user_id, title, author, cover_path, created_at, updated_at) VALUES (:user_id, :title, :author, NULL, :created_at, :updated_at)');
    $stmt->execute([
        ':user_id' => (int)current_user_id(),
        ':title' => $title,
        ':author' => $author,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $bookId = (int)db()->lastInsertId();
    $stmt = db()->prepare('INSERT INTO pages (book_id, page_index, drawing_path, image_path, text, text_position, created_at, updated_at) VALUES (:book_id, :page_index, NULL, NULL, :text, :text_position, :created_at, :updated_at)');
    $stmt->execute([
        ':book_id' => $bookId,
        ':page_index' => -2,
        ':text' => '',
        ':text_position' => 'below',
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    $stmt->execute([
        ':book_id' => $bookId,
        ':page_index' => -1,
        ':text' => '',
        ':text_position' => 'below',
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    flash_set('success', 'Libro creado.');
    redirect('/library');
}

if ($path === '/library' && $method === 'GET') {
    require_auth();
    $stmt = db()->prepare('SELECT id, title, author, updated_at FROM books WHERE user_id = :user_id ORDER BY updated_at DESC, id DESC');
    $stmt->execute([':user_id' => (int)current_user_id()]);
    $books = $stmt->fetchAll();
    render('books/library', ['title' => 'Biblioteca', 'books' => $books]);
    exit;
}

if ($path === '/edit' && $method === 'GET') {
    require_auth();
    $selectedBookId = $_GET['book_id'] ?? null;

    if ($selectedBookId !== null) {
        $bookId = filter_var($selectedBookId, FILTER_VALIDATE_INT);
        if ($bookId === false) {
            flash_set('error', 'Libro inválido.');
            redirect('/library');
        }

        $book = owned_book((int)$bookId);
        if ($book === null) {
            flash_set('error', 'No se encontró el libro.');
            redirect('/library');
        }

        $model = build_book_pages_model((int)$bookId);

        render('books/editor', [
            'title' => 'Editar',
            'book' => $book,
            'cover' => $model['cover'],
            'backCover' => $model['backCover'],
            'sheets' => $model['sheets'],
        ]);
        exit;
    }

    $stmt = db()->prepare('SELECT id, title, author, updated_at FROM books WHERE user_id = :user_id ORDER BY updated_at DESC, id DESC');
    $stmt->execute([':user_id' => (int)current_user_id()]);
    $books = $stmt->fetchAll();
    render('books/edit-select', ['title' => 'Editar', 'books' => $books, 'selectedBookId' => null]);
    exit;
}

if ($path === '/print' && $method === 'GET') {
    require_auth();
    $bookId = filter_var($_GET['book_id'] ?? null, FILTER_VALIDATE_INT);
    if ($bookId === false) {
        flash_set('error', 'Libro inválido.');
        redirect('/library');
    }

    $book = owned_book((int)$bookId);
    if ($book === null) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    $model = build_book_pages_model((int)$bookId);
    render('books/print', [
        'title' => 'Impresión',
        'layoutMode' => 'print',
        'book' => $book,
        'cover' => $model['cover'],
        'backCover' => $model['backCover'],
        'sheets' => $model['sheets'],
    ]);
    exit;
}

if ($path === '/pdf' && $method === 'GET') {
    require_auth();
    $bookId = filter_var($_GET['book_id'] ?? null, FILTER_VALIDATE_INT);
    if ($bookId === false) {
        flash_set('error', 'Libro inválido.');
        redirect('/library');
    }

    $book = owned_book((int)$bookId);
    if ($book === null) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    $autoload = APP_ROOT . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Dependencias no instaladas';
        exit;
    }

    require_once $autoload;

    $model = build_book_pages_model((int)$bookId);

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0, true);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetCreator('Papelitos');
    $pdf->SetAuthor('Papelitos');
    $pdf->SetTitle((string)($book['title'] ?? 'Papelitos'));
    $pdf->SetFont('helvetica', '', 11);

    $pageW = 210.0;
    $pageH = 297.0;
    $halfW = $pageW / 2.0;
    $pad = 8.0;

    $drawHalf = function (?array $p, float $x, float $y, float $w, float $h, string $label) use ($pdf, $pad): void {
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($x + $pad, $y + $pad);
        $pdf->Cell($w - ($pad * 2), 6, $label, 0, 1, 'L', false, '', 0, false, 'T', 'M');

        $pdf->SetFont('helvetica', '', 11);
        $curY = $y + $pad + 8;
        $imgMaxH = $h * 0.55;

        $imagePath = is_array($p) ? (string)($p['image_path'] ?? '') : '';
        if ($imagePath !== '') {
            $filePath = rtrim(APP_DATA_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $imagePath;
            if (is_file($filePath)) {
                $pdf->Image($filePath, $x + $pad, $curY, $w - ($pad * 2), $imgMaxH, '', '', '', true, 300, '', false, false, 0, true, false, false);
                $curY = $curY + $imgMaxH + 6;
            }
        }

        $text = is_array($p) ? (string)($p['text'] ?? '') : '';
        $pdf->SetXY($x + $pad, $curY);
        $pdf->MultiCell($w - ($pad * 2), $h - ($curY - $y) - $pad, $text, 0, 'L', false, 1, '', '', true);
    };

    $addSheet = function (?array $left, ?array $right, string $leftLabel, string $rightLabel) use ($pdf, $drawHalf, $pageW, $pageH, $halfW): void {
        $pdf->AddPage('P', 'A4');
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->Line($halfW, 0, $halfW, $pageH);
        $drawHalf($left, 0, 0, $halfW, $pageH, $leftLabel);
        $drawHalf($right, $halfW, 0, $halfW, $pageH, $rightLabel);
    };

    $addSheet($model['cover'] ?? null, $model['backCover'] ?? null, 'Tapa', 'Contratapa');

    $sheets = $model['sheets'] ?? [];
    if (is_array($sheets)) {
        foreach ($sheets as $s) {
            if (!is_array($s)) {
                continue;
            }
            $sheetIndex = (int)($s['sheet_index'] ?? 0);
            $left = is_array($s['left'] ?? null) ? $s['left'] : null;
            $right = is_array($s['right'] ?? null) ? $s['right'] : null;
            $nLeft = $sheetIndex * 2 + 1;
            $nRight = $sheetIndex * 2 + 2;
            $addSheet($left, $right, 'Página ' . $nLeft, 'Página ' . $nRight);
        }
    }

    $filename = 'papelitos_' . (int)$bookId . '.pdf';
    $content = $pdf->Output($filename, 'S');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

if ($path === '/page/add' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookIdRaw = $_POST['book_id'] ?? null;
    $bookId = filter_var($bookIdRaw, FILTER_VALIDATE_INT);
    if ($bookId === false) {
        flash_set('error', 'Libro inválido.');
        redirect('/library');
    }

    $stmt = db()->prepare('SELECT id FROM books WHERE id = :book_id AND user_id = :user_id LIMIT 1');
    $stmt->execute([
        ':book_id' => (int)$bookId,
        ':user_id' => (int)current_user_id(),
    ]);
    $book = $stmt->fetch();
    if (!is_array($book)) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    $_POST['book_id'] = (string)$bookId;
    $_POST['_csrf'] = (string)($_POST['_csrf'] ?? '');
    $path = '/sheet/add';
}

if ($path === '/sheet/add' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookIdRaw = $_POST['book_id'] ?? null;
    $bookId = filter_var($bookIdRaw, FILTER_VALIDATE_INT);
    if ($bookId === false) {
        flash_set('error', 'Libro inválido.');
        redirect('/library');
    }

    $book = owned_book((int)$bookId);
    if ($book === null) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    ensure_cover_pages((int)$bookId);
    $storyPages = normalize_story_pages((int)$bookId);
    $count = count($storyPages);

    $now = now_atom();
    $insert = db()->prepare('INSERT INTO pages (book_id, page_index, drawing_path, image_path, text, text_position, created_at, updated_at) VALUES (:book_id, :page_index, NULL, NULL, :text, :text_position, :created_at, :updated_at)');

    if (($count % 2) !== 0) {
        $insert->execute([
            ':book_id' => (int)$bookId,
            ':page_index' => $count,
            ':text' => '',
            ':text_position' => 'below',
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $count++;
    }

    $insert->execute([
        ':book_id' => (int)$bookId,
        ':page_index' => $count,
        ':text' => '',
        ':text_position' => 'below',
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    $insert->execute([
        ':book_id' => (int)$bookId,
        ':page_index' => $count + 1,
        ':text' => '',
        ':text_position' => 'below',
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $stmt = db()->prepare('UPDATE books SET updated_at = :updated_at WHERE id = :book_id');
    $stmt->execute([
        ':updated_at' => now_atom(),
        ':book_id' => (int)$bookId,
    ]);

    flash_set('success', 'Hoja agregada.');
    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if ($path === '/sheet/delete' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $sheetIndex = filter_var($_POST['sheet_index'] ?? null, FILTER_VALIDATE_INT);
    if ($bookId === false || $sheetIndex === false || $sheetIndex < 0) {
        flash_set('error', 'Hoja inválida.');
        redirect('/library');
    }

    $book = owned_book((int)$bookId);
    if ($book === null) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    normalize_story_pages((int)$bookId);

    $start = (int)$sheetIndex * 2;
    $stmt = db()->prepare('SELECT p.id FROM pages p INNER JOIN books b ON b.id = p.book_id WHERE p.book_id = :book_id AND p.page_index IN (:a, :b) AND b.user_id = :user_id');
    $stmt->execute([
        ':book_id' => (int)$bookId,
        ':a' => $start,
        ':b' => $start + 1,
        ':user_id' => (int)current_user_id(),
    ]);
    $rows = $stmt->fetchAll();
    if (!is_array($rows) || count($rows) === 0) {
        flash_set('error', 'No se encontró la hoja.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    db()->beginTransaction();
    $del = db()->prepare('DELETE FROM pages WHERE id = :id');
    foreach ($rows as $r) {
        if (is_array($r) && isset($r['id'])) {
            $del->execute([':id' => (int)$r['id']]);
        }
    }
    db()->commit();

    normalize_story_pages((int)$bookId);

    $stmt = db()->prepare('UPDATE books SET updated_at = :updated_at WHERE id = :book_id');
    $stmt->execute([
        ':updated_at' => now_atom(),
        ':book_id' => (int)$bookId,
    ]);

    flash_set('success', 'Hoja eliminada.');
    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if ($path === '/sheet/move' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $sheetIndex = filter_var($_POST['sheet_index'] ?? null, FILTER_VALIDATE_INT);
    $direction = (string)($_POST['direction'] ?? '');
    if ($bookId === false || $sheetIndex === false || $sheetIndex < 0 || !in_array($direction, ['up', 'down'], true)) {
        flash_set('error', 'Movimiento inválido.');
        redirect('/library');
    }

    $book = owned_book((int)$bookId);
    if ($book === null) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    $storyPages = normalize_story_pages((int)$bookId);
    $count = count($storyPages);
    $sheetCount = (int)floor($count / 2);
    if ($sheetCount <= 1) {
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $from = (int)$sheetIndex;
    $to = $direction === 'up' ? $from - 1 : $from + 1;
    if ($to < 0 || $to >= $sheetCount) {
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $a0 = $from * 2;
    $a1 = $from * 2 + 1;
    $b0 = $to * 2;
    $b1 = $to * 2 + 1;

    $stmt = db()->prepare('SELECT id, page_index FROM pages WHERE book_id = :book_id AND page_index IN (:a0, :a1, :b0, :b1) ORDER BY page_index ASC');
    $stmt->execute([
        ':book_id' => (int)$bookId,
        ':a0' => $a0,
        ':a1' => $a1,
        ':b0' => $b0,
        ':b1' => $b1,
    ]);
    $rows = $stmt->fetchAll();
    if (!is_array($rows) || count($rows) !== 4) {
        flash_set('error', 'No se pudo mover la hoja.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $ids = [];
    foreach ($rows as $r) {
        if (is_array($r) && isset($r['id'], $r['page_index'])) {
            $ids[(int)$r['page_index']] = (int)$r['id'];
        }
    }
    foreach ([$a0, $a1, $b0, $b1] as $pi) {
        if (!isset($ids[$pi])) {
            flash_set('error', 'No se pudo mover la hoja.');
            redirect('/edit?book_id=' . urlencode((string)$bookId));
        }
    }

    db()->beginTransaction();
    $tmpOffset = 10000;
    $now = now_atom();
    $updTmp = db()->prepare('UPDATE pages SET page_index = :page_index, updated_at = :updated_at WHERE id = :id');
    $updTmp->execute([':page_index' => $a0 + $tmpOffset, ':updated_at' => $now, ':id' => $ids[$a0]]);
    $updTmp->execute([':page_index' => $a1 + $tmpOffset, ':updated_at' => $now, ':id' => $ids[$a1]]);
    $updTmp->execute([':page_index' => $a0, ':updated_at' => $now, ':id' => $ids[$b0]]);
    $updTmp->execute([':page_index' => $a1, ':updated_at' => $now, ':id' => $ids[$b1]]);
    $updTmp->execute([':page_index' => $b0, ':updated_at' => $now, ':id' => $ids[$a0]]);
    $updTmp->execute([':page_index' => $b1, ':updated_at' => $now, ':id' => $ids[$a1]]);
    db()->commit();

    $stmt = db()->prepare('UPDATE books SET updated_at = :updated_at WHERE id = :book_id');
    $stmt->execute([
        ':updated_at' => now_atom(),
        ':book_id' => (int)$bookId,
    ]);

    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if ($path === '/sheet/swap' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $sheetIndex = filter_var($_POST['sheet_index'] ?? null, FILTER_VALIDATE_INT);
    if ($bookId === false || $sheetIndex === false || $sheetIndex < 0) {
        flash_set('error', 'Hoja inválida.');
        redirect('/library');
    }

    $book = owned_book((int)$bookId);
    if ($book === null) {
        flash_set('error', 'No se encontró el libro.');
        redirect('/library');
    }

    normalize_story_pages((int)$bookId);

    $a0 = (int)$sheetIndex * 2;
    $a1 = $a0 + 1;

    $stmt = db()->prepare('SELECT p.id, p.page_index FROM pages p INNER JOIN books b ON b.id = p.book_id WHERE p.book_id = :book_id AND p.page_index IN (:a0, :a1) AND b.user_id = :user_id ORDER BY p.page_index ASC');
    $stmt->execute([
        ':book_id' => (int)$bookId,
        ':a0' => $a0,
        ':a1' => $a1,
        ':user_id' => (int)current_user_id(),
    ]);
    $rows = $stmt->fetchAll();
    if (!is_array($rows) || count($rows) !== 2) {
        flash_set('error', 'No se pudo intercambiar.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $ids = [];
    foreach ($rows as $r) {
        if (is_array($r) && isset($r['id'], $r['page_index'])) {
            $ids[(int)$r['page_index']] = (int)$r['id'];
        }
    }
    if (!isset($ids[$a0], $ids[$a1])) {
        flash_set('error', 'No se pudo intercambiar.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $now = now_atom();
    db()->beginTransaction();
    $upd = db()->prepare('UPDATE pages SET page_index = :page_index, updated_at = :updated_at WHERE id = :id');
    $tmp = 10000;
    $upd->execute([':page_index' => $a0 + $tmp, ':updated_at' => $now, ':id' => $ids[$a0]]);
    $upd->execute([':page_index' => $a0, ':updated_at' => $now, ':id' => $ids[$a1]]);
    $upd->execute([':page_index' => $a1, ':updated_at' => $now, ':id' => $ids[$a0]]);
    db()->commit();

    $stmt = db()->prepare('UPDATE books SET updated_at = :updated_at WHERE id = :book_id');
    $stmt->execute([
        ':updated_at' => now_atom(),
        ':book_id' => (int)$bookId,
    ]);

    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if ($path === '/page/update' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookIdRaw = $_POST['book_id'] ?? null;
    $pageIdRaw = $_POST['page_id'] ?? null;
    $bookId = filter_var($bookIdRaw, FILTER_VALIDATE_INT);
    $pageId = filter_var($pageIdRaw, FILTER_VALIDATE_INT);
    if ($bookId === false || $pageId === false) {
        flash_set('error', 'Página inválida.');
        redirect('/library');
    }

    $text = trim((string)($_POST['text'] ?? ''));
    if (mb_strlen($text) > 2000) {
        flash_set('error', 'El texto es demasiado largo (máx 2000 caracteres).');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $stmt = db()->prepare('SELECT p.id FROM pages p INNER JOIN books b ON b.id = p.book_id WHERE p.id = :page_id AND p.book_id = :book_id AND b.user_id = :user_id LIMIT 1');
    $stmt->execute([
        ':page_id' => (int)$pageId,
        ':book_id' => (int)$bookId,
        ':user_id' => (int)current_user_id(),
    ]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        flash_set('error', 'No se encontró la página.');
        redirect('/library');
    }

    $stmt = db()->prepare('UPDATE pages SET text = :text, updated_at = :updated_at WHERE id = :page_id');
    $stmt->execute([
        ':text' => $text,
        ':updated_at' => now_atom(),
        ':page_id' => (int)$pageId,
    ]);

    flash_set('success', 'Texto guardado.');
    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if ($path === '/page/delete' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $pageId = filter_var($_POST['page_id'] ?? null, FILTER_VALIDATE_INT);
    if ($bookId === false || $pageId === false) {
        flash_set('error', 'Página inválida.');
        redirect('/library');
    }

    $stmt = db()->prepare('SELECT p.id, p.page_index FROM pages p INNER JOIN books b ON b.id = p.book_id WHERE p.id = :page_id AND p.book_id = :book_id AND b.user_id = :user_id LIMIT 1');
    $stmt->execute([
        ':page_id' => (int)$pageId,
        ':book_id' => (int)$bookId,
        ':user_id' => (int)current_user_id(),
    ]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        flash_set('error', 'No se encontró la página.');
        redirect('/library');
    }

    $pageIndex = (int)($row['page_index'] ?? -9999);
    if ($pageIndex < 0) {
        flash_set('error', 'No se puede eliminar tapa/contratapa.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $stmt = db()->prepare('DELETE FROM pages WHERE id = :page_id');
    $stmt->execute([':page_id' => (int)$pageId]);

    normalize_story_pages((int)$bookId);

    $stmt = db()->prepare('UPDATE books SET updated_at = :updated_at WHERE id = :book_id');
    $stmt->execute([
        ':updated_at' => now_atom(),
        ':book_id' => (int)$bookId,
    ]);

    flash_set('success', 'Página eliminada.');
    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if ($path === '/page/image' && $method === 'POST') {
    require_auth();
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('error', 'No se pudo validar el formulario.');
        redirect('/library');
    }

    $bookIdRaw = $_POST['book_id'] ?? null;
    $pageIdRaw = $_POST['page_id'] ?? null;
    $bookId = filter_var($bookIdRaw, FILTER_VALIDATE_INT);
    $pageId = filter_var($pageIdRaw, FILTER_VALIDATE_INT);
    if ($bookId === false || $pageId === false) {
        flash_set('error', 'Página inválida.');
        redirect('/library');
    }

    $stmt = db()->prepare('SELECT p.id FROM pages p INNER JOIN books b ON b.id = p.book_id WHERE p.id = :page_id AND p.book_id = :book_id AND b.user_id = :user_id LIMIT 1');
    $stmt->execute([
        ':page_id' => (int)$pageId,
        ':book_id' => (int)$bookId,
        ':user_id' => (int)current_user_id(),
    ]);
    $row = $stmt->fetch();
    if (!is_array($row)) {
        flash_set('error', 'No se encontró la página.');
        redirect('/library');
    }

    if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        flash_set('error', 'No se recibió ninguna imagen.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $file = $_FILES['image'];
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        flash_set('error', 'No se pudo subir la imagen.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    if ($tmpName === '' || $size <= 0) {
        flash_set('error', 'Imagen inválida.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    if ($size > 10 * 1024 * 1024) {
        flash_set('error', 'La imagen es demasiado pesada (máx 10MB).');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $ext = $map[$mime] ?? null;
    if ($ext === null) {
        flash_set('error', 'Formato de imagen no soportado.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $uploadsDir = rtrim(APP_DATA_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0777, true);
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $uploadsDir . DIRECTORY_SEPARATOR . $name;

    if (!move_uploaded_file($tmpName, $dest)) {
        flash_set('error', 'No se pudo guardar la imagen.');
        redirect('/edit?book_id=' . urlencode((string)$bookId));
    }

    $stmt = db()->prepare('UPDATE pages SET image_path = :image_path, updated_at = :updated_at WHERE id = :page_id');
    $stmt->execute([
        ':image_path' => $name,
        ':updated_at' => now_atom(),
        ':page_id' => (int)$pageId,
    ]);

    $stmt = db()->prepare('UPDATE books SET updated_at = :updated_at WHERE id = :book_id');
    $stmt->execute([
        ':updated_at' => now_atom(),
        ':book_id' => (int)$bookId,
    ]);

    flash_set('success', 'Imagen guardada.');
    redirect('/edit?book_id=' . urlencode((string)$bookId));
}

if (in_array($path, ['/help'], true) && $method === 'GET') {
    require_auth();

    $map = [
        '/help' => ['Ayuda', 'Guía de uso (próximamente).'],
    ];

    [$heading, $message] = $map[$path];
    render('placeholder', [
        'title' => $heading,
        'heading' => $heading,
        'message' => $message,
        'backTo' => '/menu',
    ]);
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404';

