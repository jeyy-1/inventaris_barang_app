<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$pageTitle = 'Dashboard';

$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalStock = (int)$pdo->query('SELECT COALESCE(SUM(stock),0) FROM products')->fetchColumn();
$lowStock = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn();
$recent = $pdo->query('SELECT t.*, p.name AS product_name, p.sku, u.name AS user_name FROM transactions t JOIN products p ON p.id=t.product_id JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC, t.id DESC LIMIT 8')->fetchAll();
$lowProducts = $pdo->query('SELECT name, sku, stock FROM products WHERE stock <= 5 ORDER BY stock ASC, name ASC LIMIT 5')->fetchAll();
require __DIR__ . '/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">RINGKASAN</p><h1>Dashboard</h1><p class="muted">Halo, <?= e($_SESSION['user']['name']) ?>. Berikut kondisi inventaris saat ini.</p></div><a class="btn btn-primary" href="transactions.php">+ Catat transaksi</a></div>
<div class="stats-grid">
  <article class="stat-card"><span class="stat-label">Total Produk</span><strong><?= $totalProducts ?></strong><span class="stat-note">jenis produk terdaftar</span></article>
  <article class="stat-card"><span class="stat-label">Kategori</span><strong><?= $totalCategories ?></strong><span class="stat-note">kategori tersedia</span></article>
  <article class="stat-card"><span class="stat-label">Total Stok</span><strong><?= number_format($totalStock,0,',','.') ?></strong><span class="stat-note">unit barang</span></article>
  <article class="stat-card <?= $lowStock ? 'stat-warning' : '' ?>"><span class="stat-label">Stok Menipis</span><strong><?= $lowStock ?></strong><span class="stat-note">produk dengan stok ≤ 5</span></article>
</div>
<div class="two-col">
<section class="panel"><div class="panel-heading"><h2>Transaksi Terbaru</h2><a href="transactions.php">Lihat semua →</a></div>
<?php if (!$recent): ?><p class="empty">Belum ada transaksi.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Petugas</th></tr></thead><tbody>
<?php foreach ($recent as $t): ?><tr><td><?= e(date('d/m/Y H:i', strtotime($t['created_at']))) ?></td><td><?= e($t['product_name']) ?><small class="cell-sub"><?= e($t['sku']) ?></small></td><td><span class="pill <?= $t['type']==='masuk'?'pill-green':'pill-red' ?>"><?= e(ucfirst($t['type'])) ?></span></td><td><?= (int)$t['quantity'] ?></td><td><?= e($t['user_name']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<section class="panel"><div class="panel-heading"><h2>Perlu Diperhatikan</h2><a href="products.php?stock=low">Lihat produk →</a></div>
<?php if (!$lowProducts): ?><p class="empty">Semua stok produk lebih dari 5 unit.</p><?php else: ?>
<div class="low-list"><?php foreach ($lowProducts as $p): ?><div class="low-item"><div><strong><?= e($p['name']) ?></strong><small><?= e($p['sku']) ?></small></div><span class="stock-number"><?= (int)$p['stock'] ?> unit</span></div><?php endforeach; ?></div><?php endif; ?>
<div class="quick-actions"><h3>Aksi cepat</h3><a href="products.php">Kelola produk</a><a href="categories.php">Kelola kategori</a><a href="transactions.php">Barang masuk/keluar</a></div>
</section></div>
<?php require __DIR__ . '/footer.php'; ?>
