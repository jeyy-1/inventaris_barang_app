CREATE DATABASE IF NOT EXISTS inventaris_barang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventaris_barang;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  sku VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_product_price CHECK (price >= 0)
) ENGINE=InnoDB;

CREATE TABLE transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('masuk','keluar') NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_product FOREIGN KEY (product_id) REFERENCES products(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_transaction_quantity CHECK (quantity > 0),
  INDEX idx_transactions_created_at (created_at),
  INDEX idx_transactions_type (type)
) ENGINE=InnoDB;

INSERT INTO categories (name, description) VALUES
('Elektronik', 'Perangkat elektronik dan komputer'),
('ATK', 'Alat tulis kantor'),
('Perlengkapan', 'Perlengkapan umum');

INSERT INTO products (category_id, sku, name, description, price, stock) VALUES
(1, 'ELK-001', 'Mouse USB', 'Mouse kabel USB', 75000, 20),
(1, 'ELK-002', 'Keyboard USB', 'Keyboard standar', 125000, 12),
(2, 'ATK-001', 'Buku Tulis', 'Buku tulis 38 lembar', 5000, 50),
(3, 'PRL-001', 'Kertas A4', 'Kertas A4 satu rim', 55000, 15);
