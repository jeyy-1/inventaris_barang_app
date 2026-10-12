<?php
require_once __DIR__ . '/helpers.php';

function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        flash('warning', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}
function requireRole(string ...$roles): void {
    requireLogin();
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        $pageTitle = 'Akses ditolak';
        require __DIR__ . '/header.php';
        echo '<section class="panel"><h1>403 — Akses ditolak</h1><p>Akun Anda tidak memiliki izin untuk membuka halaman ini.</p><a class="btn" href="index.php">Kembali ke dashboard</a></section>';
        require __DIR__ . '/footer.php';
        exit;
    }
}
function isAdmin(): bool {
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}
