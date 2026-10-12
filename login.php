<?php
require_once __DIR__ . '/helpers.php';
if (!empty($_SESSION['user'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, username, password, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($user['password']);
            $_SESSION['user'] = $user;
            flash('success', 'Login berhasil. Selamat datang, ' . $user['name'] . '!');
            redirect('index.php');
        }
        $error = 'Username atau password salah.';
    }
}
$pageTitle = 'Login';
require __DIR__ . '/header.php';
?>
<div class="login-wrap">
  <section class="login-card">
    <div class="login-icon">IB</div>
    <h1>Masuk ke Inventaris</h1>
    <p class="muted">Login untuk mengelola data dan stok barang.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-stack">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <label>Username<input name="username" autocomplete="username" required maxlength="50" value="<?= e($_POST['username'] ?? '') ?>"></label>
      <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
      <button class="btn btn-primary btn-block" type="submit">Login</button>
    </form>
    <p class="hint">Akun demo dibuat dengan menjalankan <code>seed_users.php</code> sekali. Lihat README.</p>
  </section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
