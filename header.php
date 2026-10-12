<?php
require_once __DIR__ . '/helpers.php';
$pageTitle = $pageTitle ?? 'Inventaris Barang';
$flashMessage = getFlash();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Inventaris Barang</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-mark">IB</span><span>Inventaris Barang<small>Manajemen stok sederhana</small></span></a>
  <?php if (!empty($_SESSION['user'])): ?>
  <div class="userbox"><span><?= e($_SESSION['user']['name']) ?> <small><?= e(strtoupper($_SESSION['user']['role'])) ?></small></span><a class="btn btn-outline btn-sm" href="logout.php">Keluar</a></div>
  <?php endif; ?>
</header>
<?php if (!empty($_SESSION['user'])): ?>
<nav class="nav">
  <a class="<?= currentPage()==='index.php'?'active':'' ?>" href="index.php">Dashboard</a>
  <a class="<?= currentPage()==='categories.php'?'active':'' ?>" href="categories.php">Kategori</a>
  <a class="<?= currentPage()==='products.php'?'active':'' ?>" href="products.php">Produk</a>
  <a class="<?= currentPage()==='transactions.php'?'active':'' ?>" href="transactions.php">Transaksi Stok</a>
</nav>
<?php endif; ?>
<main class="container">
<?php if ($flashMessage): ?><div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
