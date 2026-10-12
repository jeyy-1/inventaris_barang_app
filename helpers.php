<?php
require_once __DIR__ . '/config.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function getFlash(): ?array {
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Permintaan tidak valid atau sesi formulir kedaluwarsa. Silakan kembali dan coba lagi.');
    }
}
function money($value): string {
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}
function currentPage(): string {
    return basename($_SERVER['PHP_SELF'] ?? 'index.php');
}


function renderPagination(int $page, int $pages, string $path, array $params = []): string {
    if ($pages <= 1) return '';
    $html = '<div class="pagination"><span>Halaman '.(int)$page.' dari '.(int)$pages.'</span><div class="pagination-links">';
    for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++) {
        $query = http_build_query(array_merge($params, ['page' => $i]));
        if ($i === $page) $html .= '<span class="current">'.$i.'</span>';
        else $html .= '<a href="'.htmlspecialchars($path.'?'.$query, ENT_QUOTES, 'UTF-8').'">'.$i.'</a>';
    }
    $html .= '</div></div>';
    return $html;
}
