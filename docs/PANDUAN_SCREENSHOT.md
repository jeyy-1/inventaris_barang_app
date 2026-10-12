# Panduan Screenshot Fitur

Screenshot harus diambil dari aplikasi yang benar-benar berjalan di browser, bukan gambar ilustrasi.

1. **Login** — buka `login.php`, tampilkan formulir login. Jangan tampilkan password yang sensitif.
2. **Dashboard** — login sebagai admin dan ambil layar statistik, transaksi terbaru, serta stok menipis.
3. **Kategori** — tampilkan formulir tambah kategori dan tabel daftar kategori.
4. **Produk** — tampilkan form produk dan tabel. Ambil screenshot lain saat filter kategori/stok diterapkan.
5. **Search/sorting/pagination** — cari kata kunci, pilih sorting, lalu tampilkan navigasi halaman jika data lebih dari satu halaman.
6. **Barang masuk** — catat transaksi masuk, lalu screenshot pesan sukses dan stok produk setelah diperbarui.
7. **Barang keluar** — catat transaksi keluar dan tampilkan stok terbaru serta riwayat.
8. **Validasi error** — coba barang keluar melebihi stok atau jumlah transaksi 0; screenshot pesan validasi.
9. **Akses terlarang** — login sebagai staff. Screenshot halaman produk tanpa tombol CRUD; untuk bukti 403 server, lakukan pengujian POST terlarang dan screenshot hasil HTTP 403.
10. Simpan gambar dengan nama yang jelas, misalnya `01-login.png`, `02-dashboard.png`, `03-kategori.png`, dan seterusnya.

Untuk Windows, tekan `Win + Shift + S` untuk memilih area layar. Pastikan nama aplikasi/halaman terlihat dan jangan memasukkan data pribadi yang tidak diperlukan.
