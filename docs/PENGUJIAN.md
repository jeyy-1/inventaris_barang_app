# Skenario Pengujian

Lakukan pengujian pada aplikasi yang sudah diinstal. Catat hasil aktual dan ambil screenshot browser sebagai bukti.

| ID | Skenario | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| T01 | Login berhasil | Login `admin` dengan password `admin123` | Masuk ke dashboard |
| T02 | Login gagal | Isi password salah | Pesan username/password salah; tidak masuk |
| T03 | Validasi kategori kosong | Login admin, kirim formulir kategori dengan nama kosong | Browser menolak input wajib; bila request dimanipulasi server juga menolak |
| T04 | SKU duplikat | Admin menambah produk dengan SKU yang sudah ada | Pesan data tidak valid/SKU sudah digunakan; data tidak bertambah |
| T05 | Harga negatif | Coba isi harga -100 | Validasi HTML/server menolak harga |
| T06 | Jumlah transaksi nol | Catat transaksi dengan jumlah 0 | Pesan jumlah minimal 1; stok tidak berubah |
| T07 | Stok keluar berlebihan | Pilih produk stok 5, catat keluar 6 | Pesan stok tidak cukup; stok dan riwayat tidak berubah |
| T08 | Staff mencoba edit produk | Login sebagai `staff`, buka `products.php` | Form edit/tambah tidak ditampilkan; akses perubahan ditolak server |
| T09 | Staff mengirim POST terlarang | Login staff, kirim POST ke `products.php` dengan action create dan token CSRF valid | HTTP 403; produk tidak berubah |
| T10 | Filter dan pagination | Cari produk, pilih kategori/stok, lalu pindah halaman | Hasil mengikuti filter dan halaman |
| T11 | Barang masuk | Catat barang masuk sebanyak 3 | Stok bertambah 3 dan transaksi tersimpan |
| T12 | Barang keluar | Catat barang keluar sebanyak 2 jika stok cukup | Stok berkurang 2 dan transaksi tersimpan |

## Bukti akses terlarang
1. Login dengan akun staff.
2. Buka `products.php`; pastikan formulir mutasi data tidak muncul.
3. Untuk menguji pemeriksaan server, gunakan alat pengujian HTTP/browser developer tools untuk mengirim POST ke `products.php` dengan `action=create`, data produk, dan token CSRF yang valid dari sesi staff.
4. Hasil yang diharapkan: HTTP 403 dan tidak ada produk baru.
5. Ulangi untuk `categories.php`.

Jangan menyimpulkan tes sudah berhasil sebelum menjalankannya dan melihat hasil aktual.
