<?php
require_once __DIR__ . '/auth.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!isAdmin()) { http_response_code(403); exit('403 — Staff tidak diizinkan mengubah kategori.'); }
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id=?');
            $stmt->execute([$id]);
            flash('success', 'Kategori berhasil dihapus.');
        } catch (PDOException $e) {
            flash('danger', 'Kategori tidak dapat dihapus karena masih digunakan produk.');
        }
        redirect('categories.php');
    }
    if ($name === '' || mb_strlen($name) > 100) {
        flash('danger', 'Nama kategori wajib diisi dan maksimal 100 karakter.');
        redirect('categories.php' . ($id ? '?edit='.$id : ''));
    }
    try {
        if ($action === 'update' && $id > 0) {
            $stmt = $pdo->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
            $stmt->execute([$name, $description ?: null, $id]);
            flash('success', 'Kategori berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description ?: null]);
            flash('success', 'Kategori berhasil ditambahkan.');
        }
    } catch (PDOException $e) {
        flash('danger', 'Nama kategori sudah digunakan atau data tidak valid.');
    }
    redirect('categories.php');
}
$edit = null;
if (isset($_GET['edit']) && isAdmin()) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$where = ''; $params = [];
if ($q !== '') { $where = 'WHERE c.name LIKE ? OR c.description LIKE ?'; $params = ["%$q%", "%$q%"]; }
$stmt = $pdo->prepare("SELECT COUNT(*) FROM categories c $where"); $stmt->execute($params); $total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage)); $page = min($page, $pages); $offset = ($page-1)*$perPage;
$stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count FROM categories c $where ORDER BY c.name ASC LIMIT $perPage OFFSET $offset");
$stmt->execute($params); $categories = $stmt->fetchAll();
$pageTitle = 'Kategori'; require __DIR__ . '/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">DATA MASTER</p><h1>Kategori</h1><p class="muted">Kelompokkan produk agar data lebih mudah dikelola.</p></div></div>
<?php if (isAdmin()): ?><section class="panel form-panel"><h2><?= $edit ? 'Edit kategori' : 'Tambah kategori' ?></h2><form method="post" class="inline-form">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="<?= $edit?'update':'create' ?>"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<label>Nama kategori<input name="name" required maxlength="100" value="<?= e($edit['name'] ?? '') ?>" placeholder="Contoh: Elektronik"></label>
<label>Deskripsi<input name="description" maxlength="255" value="<?= e($edit['description'] ?? '') ?>" placeholder="Keterangan singkat"></label>
<button class="btn btn-primary" type="submit"><?= $edit?'Simpan perubahan':'Tambah kategori' ?></button><?php if ($edit): ?><a class="btn btn-outline" href="categories.php">Batal</a><?php endif; ?>
</form></section><?php else: ?><div class="notice">Mode staff: Anda dapat melihat kategori, tetapi tidak dapat mengubahnya.</div><?php endif; ?>
<section class="panel"><form method="get" class="toolbar"><input name="q" value="<?= e($q) ?>" placeholder="Cari nama/deskripsi kategori..."><button class="btn btn-outline" type="submit">Cari</button><?php if ($q): ?><a href="categories.php" class="btn btn-ghost">Reset</a><?php endif; ?><span class="muted push-right"><?= $total ?> kategori</span></form>
<div class="table-wrap"><table><thead><tr><th>Nama</th><th>Deskripsi</th><th>Jumlah Produk</th><?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?></tr></thead><tbody>
<?php foreach ($categories as $c): ?><tr><td><strong><?= e($c['name']) ?></strong></td><td><?= e($c['description'] ?: '—') ?></td><td><?= (int)$c['product_count'] ?></td><?php if (isAdmin()): ?><td><div class="actions"><a class="btn btn-sm btn-outline" href="categories.php?edit=<?= (int)$c['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Hapus kategori ini?')" class="form-inline"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></div></td><?php endif; ?></tr><?php endforeach; ?>
<?php if (!$categories): ?><tr><td colspan="<?= isAdmin()?4:3 ?>" class="empty">Kategori tidak ditemukan.</td></tr><?php endif; ?>
</tbody></table></div><?= renderPagination($page,$pages,'categories.php',['q'=>$q]) ?></section>
<?php require __DIR__ . '/footer.php'; ?>
