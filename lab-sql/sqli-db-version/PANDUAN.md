# Panduan Penggunaan SQL Injection Lab - Database Version Query

## Gambaran Umum
Lab ini dirancang untuk melatih peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection untuk mengambil informasi versi database pada MySQL dan Microsoft SQL Server. Aplikasi ini sengaja dibuat rentan terhadap SQL Injection di filter kategori produk menggunakan serangan UNION.

## Arsitektur Aplikasi
```
┌─────────────┐
│  Peserta    │
│  (Browser)  │
└─────┬───────┘
      │ SQL Injection
      ▼
┌─────────────┐
│ Flask App   │
│ (Vulnerable)│
└─────┬───────┘
      │ data extraction
      ▼
┌─────────────┐
│ Database    │
│ (Version)   │
└─────────────┘
```

## UI Modern dan Ramah Pengguna
Aplikasi ini dilengkapi dengan antarmuka yang:
- **Modern**: Menggunakan desain CSS terkini dengan gradient lembut
- **Ramah Pengguna**: Layout yang intuitif dan responsif
- **Petunjuk Built-in**: Informasi dan petunjuk langsung di halaman
- **Umpan Balik Visual**: Respons yang jelas saat berhasil mengambil informasi versi

## Endpoint Vulnerable
Aplikasi memiliki satu endpoint utama yang rentan terhadap SQL Injection:

**Product Category Filter** (`/`)
```python
sql_query = f"SELECT * FROM products WHERE category = '{category}'"
```

## Payload Umum
Beberapa payload SQL Injection UNION yang dapat digunakan:

- `Gifts' UNION SELECT 1,@@version,3,4,5,6 --` (untuk MySQL dan SQL Server)
- `Electronics' UNION SELECT NULL,VERSION(),NULL,NULL,NULL,NULL --`
- `Home' UNION SELECT 1,@@version,3,4,5,6 --`

## Mekanisme Penyelesaian
Untuk menyelesaikan challenge ini, peserta harus:
1. Mengakses halaman utama di http://localhost:5006
2. Menggunakan filter kategori produk
3. Menggunakan payload SQL Injection UNION pada parameter kategori
4. Berhasil menampilkan string versi database
5. Setelah berhasil, flag akan ditampilkan di UI

## Validasi Solusi
Aplikasi akan mendeteksi apakah informasi versi database berhasil ditampilkan, dan kemudian menampilkan flag.

## Pengetahuan yang Didapat
- Mengenali serangan UNION dalam SQL Injection
- Mempelajari cara mengambil informasi versi database
- Memahami pentingnya sanitasi input dalam aplikasi web
- Mengetahui bagaimana informasi metadata database dapat diakses melalui SQL injection
- Mengenal prinsip-prinsip UI/UX dalam aplikasi keamanan siber

## Tips untuk Peserta
- Perhatikan struktur query SQL yang digunakan
- Coba gunakan serangan UNION untuk menggabungkan hasil dari fungsi versi database
- Gunakan fungsi seperti @@version (untuk MySQL dan SQL Server) atau VERSION()
- Pastikan jumlah kolom dalam UNION sesuai dengan query asli
- Setelah mendapatkan flag, Anda telah berhasil mengeksploitasi informasi sistem