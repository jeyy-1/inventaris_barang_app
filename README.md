# Inventaris Barang

Aplikasi inventaris sederhana menggunakan PHP Native, PDO, dan MySQL. Cocok untuk dijalankan secara lokal dengan XAMPP.

## Fitur
- Login dan logout dengan password hash.
- Role `admin` dan `staff`.
- Dashboard jumlah produk, kategori, total stok, stok menipis, dan transaksi terbaru.
- CRUD kategori dan produk (admin).
- Pencarian produk/kategori/transaksi, filter kategori dan stok, sorting, serta pagination.
- Transaksi barang masuk dan keluar dengan validasi stok.
- Proteksi CSRF untuk formulir POST, prepared statements, escaping output, dan pemeriksaan role di sisi server.
- Data contoh kategori dan produk pada `schema.sql`.

## Persyaratan
- XAMPP (Apache + MySQL/MariaDB).
- PHP 8.1+ direkomendasikan, ekstensi PDO MySQL dan mbstring aktif.
- Browser modern.

## Instalasi di XAMPP
1. Salin folder `inventaris_barang` ke `C:\\xampp\\htdocs\\`.
2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, pilih file `schema.sql`, lalu jalankan import. File ini membuat database `inventaris_barang` beserta tabel dan data contoh.
5. Periksa `config.php`. Konfigurasi default XAMPP adalah host `localhost`, user `root`, password kosong. Sesuaikan jika MySQL Anda menggunakan password.
6. Buka `http://localhost/inventaris_barang/seed_users.php` satu kali untuk membuat akun demo.
7. Login melalui `http://localhost/inventaris_barang/login.php`.
8. Setelah akun berhasil dibuat, **hapus `seed_users.php`** dari folder proyek.

## Akun demo
- Admin: username `admin`, password `admin123`
- Staff: username `staff`, password `staff123`

Ganti password demo dan jangan gunakan kredensial ini di server publik.

## Hak akses
| Fitur | Admin | Staff |
|---|---|---|
| Dashboard | Ya | Ya |
| Lihat kategori dan produk | Ya | Ya |
| Tambah/edit/hapus kategori | Ya | Tidak |
| Tambah/edit/hapus produk | Ya | Tidak |
| Catat barang masuk/keluar | Ya | Ya |
| Lihat riwayat transaksi | Ya | Ya |

## ERD
Lihat `docs/ERD.md`. Relasi inti:
- `categories` 1 — N `products`
- `products` 1 — N `transactions`
- `users` 1 — N `transactions`

## Business rule
1. Username pengguna harus unik; password disimpan sebagai hash melalui `password_hash()`.
2. Hanya admin yang dapat mengubah data kategori dan produk.
3. SKU produk unik dan setiap produk wajib memiliki kategori yang valid.
4. Harga tidak boleh negatif; stok harus bilangan bulat nol atau lebih.
5. Stok diubah melalui transaksi masuk/keluar, bukan dengan mengedit stok saat mengubah produk.
6. Jumlah transaksi harus bilangan bulat minimal satu.
7. Barang keluar tidak boleh melebihi stok yang tersedia.
8. Perubahan stok dan penulisan riwayat transaksi dijalankan dalam satu transaksi database.
9. Kategori atau produk yang masih memiliki relasi tidak bisa dihapus, untuk menjaga riwayat dan integritas data.
10. Semua formulir POST memeriksa token CSRF dan semua query input menggunakan prepared statement.

## Pengujian manual
Lihat `docs/PENGUJIAN.md` untuk skenario validasi error dan akses terlarang.

## Panduan screenshot
Lihat `docs/PANDUAN_SCREENSHOT.md`. Screenshot asli diambil setelah aplikasi dijalankan di browser lokal; proyek ini tidak menyertakan screenshot palsu.

## Struktur folder
```text
inventaris_barang/
├── assets/style.css
├── docs/
│   ├── ERD.md
│   ├── PENGUJIAN.md
│   └── PANDUAN_SCREENSHOT.md
├── auth.php
├── categories.php
├── config.php
├── footer.php
├── helpers.php
├── index.php
├── login.php
├── logout.php
├── products.php
├── schema.sql
├── seed_users.php
└── transactions.php
```

## Catatan keamanan
Ini proyek pembelajaran untuk lingkungan lokal. Sebelum dipasang di internet, tambahkan HTTPS, manajemen password, rate limit login, audit log, kebijakan backup, dan konfigurasi server yang aman. Hapus skrip seed setelah digunakan.
