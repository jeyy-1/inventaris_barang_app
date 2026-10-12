
<?php
require_once __DIR__ . '/config.php';

$accounts = [
    ['Administrator', 'admin', 'admin123', 'admin'],
    ['Staff Gudang', 'staff', 'staff123', 'staff'],
];

foreach ($accounts as [$name, $username, $plainPassword, $role]) {
    $stmt = $pdo->prepare(
        'SELECT id FROM users WHERE username = ?'
    );
    $stmt->execute([$username]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, username, password, role)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $username,
            password_hash($plainPassword, PASSWORD_DEFAULT),
            $role
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Akun Demo Berhasil</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="container">
    <section class="login-card" style="margin:50px auto">
        <div class="login-icon">IB</div>
        <h1>Akun demo siap digunakan!</h1>
        <p class="muted">Akun login Inventaris Barang sudah tersedia.</p>

        <div class="notice">
            <strong>Admin</strong><br>
            Username: admin<br>
            Password: admin123
        </div>

        <div class="notice">
            <strong>Staff</strong><br>
            Username: staff<br>
            Password: staff123
        </div>

        <a class="btn btn-primary btn-block"
           href="login.php">Lanjut ke halaman login</a>

        <p class="hint">
            Demi keamanan, hapus file seed_users.php setelah selesai.
        </p>
    </section>
</main>
</body>
</html>

