<?php
require_once __DIR__ . '/auth.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!isAdmin()) { http_response_code(403); exit('403 — Staff tidak diizinkan mengubah data produk.'); }
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id=?');
            $stmt->execute([$id]);
            flash('success', 'Produk berhasil dihapus.');
        } catch (PDOException $e) {
            flash('danger', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }
        redirect('products.php');
    }
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $sku = strtoupper(trim((string)($_POST['sku'] ?? '')));
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $priceInput = trim((string)($_POST['price'] ?? ''));
    $stockInput = trim((string)($_POST['stock'] ?? '0'));
    $validCategory = $pdo->prepare('SELECT id FROM categories WHERE id=?'); $validCategory->execute([$categoryId]);
    $errors = [];
    if (!$validCategory->fetch()) $errors[] = 'Pilih kategori yang tersedia.';
    if ($sku === '' || mb_strlen($sku)>40) $errors[] = 'SKU wajib diisi dan maksimal 40 karakter.';
    if ($name === '' || mb_strlen($name)>150) $errors[] = 'Nama produk wajib diisi dan maksimal 150 karakter.';
    if (!is_numeric($priceInput) || (float)$priceInput < 0) $errors[] = 'Harga harus berupa angka nol atau lebih.';
    if (filter_var($stockInput, FILTER_VALIDATE_INT) === false || (int)$stockInput < 0) $errors[] = 'Stok harus bilangan bulat nol atau lebih.';
    if ($errors) {
        flash('danger', implode(' ', $errors));
        redirect('products.php' . ($id ? '?edit='.$id : ''));
    }
    try {
        if ($action === 'update' && $id > 0) {
            // Stok awal tidak boleh diedit di formulir; perubahan stok melalui transaksi.
            $stmt = $pdo->prepare('UPDATE products SET category_id=?, sku=?, name=?, description=?, price=? WHERE id=?');
            $stmt->execute([$categoryId,$sku,$name,$description ?: null,(float)$priceInput,$id]);
            flash('success', 'Produk berhasil diperbarui. Perubahan stok dilakukan melalui transaksi.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (category_id,sku,name,description,price,stock) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$categoryId,$sku,$name,$description ?: null,(float)$priceInput,(int)$stockInput]);
            flash('success', 'Produk berhasil ditambahkan.');
        }
    } catch (PDOException $e) {
        flash('danger', 'SKU sudah digunakan atau data tidak valid.');
    }
    redirect('products.php');
}
$edit = null;
if (isset($_GET['edit']) && isAdmin()) { $stmt=$pdo->prepare('SELECT * FROM products WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch() ?: null; }
$categories = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$q = trim((string)($_GET['q'] ?? '')); $categoryFilter=(int)($_GET['category_id'] ?? 0); $stockFilter=$_GET['stock'] ?? '';
$allowedSort = ['name'=>'p.name','sku'=>'p.sku','price'=>'p.price','stock'=>'p.stock','category'=>'c.name','created'=>'p.created_at'];
$sortKey = $_GET['sort'] ?? 'name'; if (!isset($allowedSort[$sortKey])) $sortKey='name';
$direction = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
$page=max(1,(int)($_GET['page'] ?? 1)); $perPage=8;
$conditions=[]; $params=[];
if ($q!=='') { $conditions[]='(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)'; array_push($params,"%$q%","%$q%","%$q%"); }
if ($categoryFilter>0) { $conditions[]='p.category_id=?'; $params[]=$categoryFilter; }
if ($stockFilter==='low') $conditions[]='p.stock <= 5';
$where=$conditions?'WHERE '.implode(' AND ',$conditions):'';
$stmt=$pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id=p.category_id $where"); $stmt->execute($params); $total=(int)$stmt->fetchColumn();
$pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages); $offset=($page-1)*$perPage;
$stmt=$pdo->prepare("SELECT p.*,c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id $where ORDER BY {$allowedSort[$sortKey]} $direction LIMIT $perPage OFFSET $offset"); $stmt->execute($params); $products=$stmt->fetchAll();
$pageTitle='Produk'; require __DIR__ . '/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">DATA MASTER</p><h1>Produk</h1><p class="muted">Kelola data barang dan pantau jumlah stok.</p></div><a class="btn btn-primary" href="transactions.php">+ Transaksi stok</a></div>
<?php if (isAdmin()): ?><section class="panel form-panel"><h2><?= $edit?'Edit produk':'Tambah produk' ?></h2><form method="post" class="product-form">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="<?= $edit?'update':'create' ?>"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<label>SKU / kode produk<input name="sku" required maxlength="40" value="<?= e($edit['sku']??'') ?>" placeholder="ELK-003"></label>
<label>Nama produk<input name="name" required maxlength="150" value="<?= e($edit['name']??'') ?>" placeholder="Nama barang"></label>
<label>Kategori<select name="category_id" required><option value="">Pilih kategori</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($edit['category_id']??0)===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
<label>Harga (Rp)<input type="number" name="price" min="0" step="0.01" required value="<?= e((string)($edit['price']??'')) ?>"></label>
<?php if (!$edit): ?><label>Stok awal<input type="number" name="stock" min="0" step="1" required value="0"></label><?php else: ?><div class="field-note">Stok saat ini: <strong><?= (int)$edit['stock'] ?></strong>. Gunakan transaksi untuk mengubah stok.</div><input type="hidden" name="stock" value="<?= (int)$edit['stock'] ?>"><?php endif; ?>
<label class="wide">Deskripsi<textarea name="description" rows="2"><?= e($edit['description']??'') ?></textarea></label>
<div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit?'Simpan perubahan':'Tambah produk' ?></button><?php if($edit): ?><a class="btn btn-outline" href="products.php">Batal</a><?php endif; ?></div>
</form></section><?php else: ?><div class="notice">Mode staff: data produk hanya dapat dilihat. Gunakan menu Transaksi Stok untuk mencatat barang masuk/keluar.</div><?php endif; ?>
<section class="panel"><form method="get" class="filters">
<input name="q" value="<?= e($q) ?>" placeholder="Cari nama, SKU, deskripsi...">
<select name="category_id"><option value="0">Semua kategori</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $categoryFilter===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
<select name="stock"><option value="">Semua stok</option><option value="low" <?= $stockFilter==='low'?'selected':'' ?>>Stok menipis (≤ 5)</option></select>
<select name="sort"><option value="name" <?= $sortKey==='name'?'selected':'' ?>>Urut nama</option><option value="sku" <?= $sortKey==='sku'?'selected':'' ?>>Urut SKU</option><option value="price" <?= $sortKey==='price'?'selected':'' ?>>Urut harga</option><option value="stock" <?= $sortKey==='stock'?'selected':'' ?>>Urut stok</option><option value="category" <?= $sortKey==='category'?'selected':'' ?>>Urut kategori</option><option value="created" <?= $sortKey==='created'?'selected':'' ?>>Terbaru</option></select>
<select name="dir"><option value="asc" <?= $direction==='ASC'?'selected':'' ?>>Naik (A–Z)</option><option value="desc" <?= $direction==='DESC'?'selected':'' ?>>Turun (Z–A)</option></select>
<button class="btn btn-outline" type="submit">Terapkan</button><a href="products.php" class="btn btn-ghost">Reset</a><span class="muted push-right"><?= $total ?> produk</span>
</form><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><?php if(isAdmin()): ?><th>Aksi</th><?php endif; ?></tr></thead><tbody>
<?php foreach($products as $p): ?><tr><td><strong><?= e($p['name']) ?></strong><small class="cell-sub"><?= e($p['sku']) ?></small></td><td><?= e($p['category_name']) ?></td><td><?= money($p['price']) ?></td><td><span class="pill <?= (int)$p['stock']<=5?'pill-red':'pill-green' ?>"><?= (int)$p['stock'] ?> unit</span></td><?php if(isAdmin()): ?><td><div class="actions"><a class="btn btn-sm btn-outline" href="products.php?edit=<?= (int)$p['id'] ?>">Edit</a><form method="post" class="form-inline" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></div></td><?php endif; ?></tr><?php endforeach; ?>
<?php if(!$products): ?><tr><td colspan="<?= isAdmin()?5:4 ?>" class="empty">Produk tidak ditemukan.</td></tr><?php endif; ?>
</tbody></table></div><?= renderPagination($page,$pages,'products.php',['q'=>$q,'category_id'=>$categoryFilter,'stock'=>$stockFilter,'sort'=>$sortKey,'dir'=>strtolower($direction)]) ?></section>
<?php require __DIR__ . '/footer.php'; ?>
