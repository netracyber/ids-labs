# Panduan Penggunaan SQL Injection Lab - Hidden Data Retrieval

## Gambaran Umum
Lab ini dirancang untuk melatih peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection pada klausa WHERE untuk mengambil data tersembunyi. Aplikasi ini sengaja dibuat rentan terhadap SQL Injection di filter kategori produk.

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
│ (Products)  │
└─────────────┘
```

## UI Modern dan Ramah Pengguna
Aplikasi ini dilengkapi dengan antarmuka yang:
- **Modern**: Menggunakan desain CSS terkini dengan gradient lembut
- **Ramah Pengguna**: Layout yang intuitif dan responsif
- **Petunjuk Built-in**: Informasi dan petunjuk langsung di halaman
- **Umpan Balik Visual**: Respons yang jelas saat berhasil mengambil data tersembunyi

## Endpoint Vulnerable
Aplikasi memiliki satu endpoint utama yang rentan terhadap SQL Injection:

**Product Category Filter** (`/`)
```python
sql_query = f"SELECT * FROM products WHERE category = '{category}' AND released = 1"
```

## Payload Umum
Beberapa payload SQL Injection yang dapat digunakan:

- `Gifts' OR 1=1 --`
- `Electronics' OR '1'='1`
- `Home' UNION SELECT NULL, name, description, price, category, released FROM products --`
- `Books' OR released = 0 --`

## Mekanisme Penyelesaian
Untuk menyelesaikan challenge ini, peserta harus:
1. Mengakses halaman utama di http://localhost:5003
2. Menggunakan filter kategori produk
3. Menggunakan payload SQL Injection pada parameter kategori
4. Berhasil menampilkan produk yang tidak dirilis (unreleased)
5. Setelah berhasil, flag dinamis akan ditampilkan di UI

## Validasi Solusi
Aplikasi akan mendeteksi apakah produk yang tidak dirilis ditampilkan, dan kemudian menampilkan flag.

## Pengetahuan yang Didapat
- Mengenali kerentanan SQL Injection pada klausa WHERE
- Membuat payload SQL Injection untuk menghindari kondisi keamanan
- Memahami pentingnya sanitasi input dalam aplikasi web
- Mengetahui bagaimana data tersembunyi dapat diakses melalui SQL injection
- Mengenal prinsip-prinsip UI/UX dalam aplikasi keamanan siber

## Tips untuk Peserta
- Perhatikan bagaimana input kategori digunakan dalam query SQL
- Coba input yang mengandung tanda kutip (') untuk melihat respons aplikasi
- Gunakan payload SQL Injection untuk mengganti kondisi 'AND released = 1'
- Pahami struktur query SQL yang rentan untuk membuat payload yang efektif
- Setelah mendapatkan flag, Anda telah berhasil menunjukkan bahwa filter keamanan bisa di-bypass