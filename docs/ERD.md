# ERD — Inventaris Barang

```mermaid
erDiagram
    USERS ||--o{ TRANSACTIONS : mencatat
    PRODUCTS ||--o{ TRANSACTIONS : memiliki
    CATEGORIES ||--o{ PRODUCTS : mengelompokkan

    USERS {
        int id PK
        varchar name
        varchar username UK
        varchar password
        enum role
        timestamp created_at
    }
    CATEGORIES {
        int id PK
        varchar name UK
        varchar description
        timestamp created_at
    }
    PRODUCTS {
        int id PK
        int category_id FK
        varchar sku UK
        varchar name
        text description
        decimal price
        int stock
        timestamp created_at
        timestamp updated_at
    }
    TRANSACTIONS {
        bigint id PK
        int product_id FK
        int user_id FK
        enum type
        int quantity
        varchar note
        timestamp created_at
    }
```

## Penjelasan relasi
- Satu kategori dapat memiliki banyak produk. Satu produk hanya terhubung ke satu kategori.
- Satu produk dapat memiliki banyak catatan transaksi.
- Satu user dapat mencatat banyak transaksi.
- Foreign key menggunakan `RESTRICT` saat penghapusan untuk melindungi data yang masih dipakai.
