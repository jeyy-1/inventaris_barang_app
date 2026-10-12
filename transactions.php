<?php
require_once __DIR__ . '/auth.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $productId=(int)($_POST['product_id']??0);
    $type=$_POST['type']??'';
    $quantityInput=trim((string)($_POST['quantity']??''));
    $note=trim((string)($_POST['note']??''));
    if (!in_array($type,['masuk','keluar'],true)) {
        flash('danger','Jenis transaksi tidak valid.'); redirect('transactions.php');
    }
    if (filter_var($quantityInput,FILTER_VALIDATE_INT)===false || (int)$quantityInput<1) {
        flash('danger','Jumlah transaksi wajib berupa bilangan bulat minimal 1.'); redirect('transactions.php');
    }
    $quantity=(int)$quantityInput;
    try {
        $pdo->beginTransaction();
        $stmt=$pdo->prepare('SELECT id, stock, name FROM products WHERE id=? FOR UPDATE');
        $stmt->execute([$productId]); $product=$stmt->fetch();
        if (!$product) throw new RuntimeException('Produk tidak ditemukan.');
        if ($type==='keluar' && $quantity>(int)$product['stock']) {
            throw new RuntimeException('Stok tidak cukup. Stok tersedia: '.(int)$product['stock'].'.');
        }
        if ($type==='masuk') {
            $update=$pdo->prepare('UPDATE products SET stock=stock+? WHERE id=?');
        } else {
            $update=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=?');
        }
        $update->execute([$quantity,$productId]);
        $insert=$pdo->prepare('INSERT INTO transactions (product_id,user_id,type,quantity,note) VALUES (?,?,?,?,?)');
        $insert->execute([$productId,(int)$_SESSION['user']['id'],$type,$quantity,$note?:null]);
        $pdo->commit();
        flash('success','Transaksi barang '.$type.' berhasil dicatat.');
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('danger',$e->getMessage());
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('danger','Transaksi gagal disimpan. Periksa data dan coba lagi.');
    }
    redirect('transactions.php');
}
$products=$pdo->query('SELECT id,sku,name,stock FROM products ORDER BY name')->fetchAll();
$q=trim((string)($_GET['q']??'')); $typeFilter=$_GET['type']??'';
$page=max(1,(int)($_GET['page']??1)); $perPage=10;
$conditions=[];$params=[];
if($q!==''){$conditions[]='(p.name LIKE ? OR p.sku LIKE ? OR u.name LIKE ? OR t.note LIKE ?)';array_push($params,"%$q%","%$q%","%$q%","%$q%");}
if(in_array($typeFilter,['masuk','keluar'],true)){$conditions[]='t.type=?';$params[]=$typeFilter;}
$where=$conditions?'WHERE '.implode(' AND ',$conditions):'';
$stmt=$pdo->prepare("SELECT COUNT(*) FROM transactions t JOIN products p ON p.id=t.product_id JOIN users u ON u.id=t.user_id $where");$stmt->execute($params);$total=(int)$stmt->fetchColumn();
$pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);$offset=($page-1)*$perPage;
$stmt=$pdo->prepare("SELECT t.*,p.name AS product_name,p.sku,u.name AS user_name FROM transactions t JOIN products p ON p.id=t.product_id JOIN users u ON u.id=t.user_id $where ORDER BY t.created_at DESC,t.id DESC LIMIT $perPage OFFSET $offset");$stmt->execute($params);$transactions=$stmt->fetchAll();
$pageTitle='Transaksi Stok';require __DIR__ . '/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">PERGERAKAN BARANG</p><h1>Transaksi Stok</h1><p class="muted">Setiap transaksi tersimpan sebagai riwayat dan memperbarui stok produk.</p></div></div>
<section class="panel form-panel"><h2>Catat transaksi baru</h2>
<?php if(!$products): ?><div class="notice">Belum ada produk. Minta admin menambahkan produk terlebih dahulu.</div><?php else: ?>
<form method="post" class="transaction-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
<label>Produk<select name="product_id" required><option value="">Pilih produk</option><?php foreach($products as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['sku'].' — '.$p['name'].' (stok: '.$p['stock'].')') ?></option><?php endforeach; ?></select></label>
<label>Jenis transaksi<select name="type" required><option value="masuk">Barang masuk</option><option value="keluar">Barang keluar</option></select></label>
<label>Jumlah<input type="number" name="quantity" min="1" step="1" required placeholder="Contoh: 5"></label>
<label>Catatan (opsional)<input name="note" maxlength="255" placeholder="Contoh: pembelian stok"></label>
<div class="form-actions"><button class="btn btn-primary" type="submit">Simpan transaksi</button></div></form><?php endif; ?>
<p class="hint">Stok keluar tidak boleh melebihi stok tersedia. Perubahan stok dicatat dalam satu transaksi database agar konsisten.</p></section>
<section class="panel"><div class="panel-heading"><h2>Riwayat transaksi</h2><span class="muted"><?= $total ?> catatan</span></div><form method="get" class="toolbar"><input name="q" value="<?= e($q) ?>" placeholder="Cari produk, SKU, petugas, catatan..."><select name="type"><option value="">Semua jenis</option><option value="masuk" <?= $typeFilter==='masuk'?'selected':'' ?>>Barang masuk</option><option value="keluar" <?= $typeFilter==='keluar'?'selected':'' ?>>Barang keluar</option></select><button class="btn btn-outline" type="submit">Cari/filter</button><a class="btn btn-ghost" href="transactions.php">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody>
<?php foreach($transactions as $t): ?><tr><td><?= e(date('d/m/Y H:i',strtotime($t['created_at']))) ?></td><td><strong><?= e($t['product_name']) ?></strong><small class="cell-sub"><?= e($t['sku']) ?></small></td><td><span class="pill <?= $t['type']==='masuk'?'pill-green':'pill-red' ?>"><?= e(ucfirst($t['type'])) ?></span></td><td><?= (int)$t['quantity'] ?></td><td><?= e($t['user_name']) ?></td><td><?= e($t['note']?:'—') ?></td></tr><?php endforeach; ?>
<?php if(!$transactions): ?><tr><td colspan="6" class="empty">Belum ada transaksi yang sesuai.</td></tr><?php endif; ?>
</tbody></table></div><?= renderPagination($page,$pages,'transactions.php',['q'=>$q,'type'=>$typeFilter]) ?></section>
<?php require __DIR__ . '/footer.php'; ?>
