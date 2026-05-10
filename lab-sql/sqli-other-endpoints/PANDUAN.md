# Panduan Penggunaan SQL Injection Lab (Other Endpoints)

## Gambaran Umum
Lab ini dirancang untuk melatih peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection pada berbagai endpoint aplikasi web, bukan hanya pada form login. Aplikasi ini sengaja dibuat rentan terhadap SQL Injection di beberapa endpoint dengan UI modern dan petunjuk built-in. Flag untuk challenge ini adalah static.

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
│ Flag Engine │
│ (1h rotate) │
└─────┬───────┘
      │
      ▼
 current_flag.txt
```

## UI Modern dan Ramah Pengguna
Aplikasi ini dilengkapi dengan antarmuka yang:
- **Modern**: Menggunakan desain CSS terkini dengan gradient lembut
- **Ramah Pengguna**: Layout yang intuitif dan responsif
- **Petunjuk Built-in**: Informasi dan petunjuk langsung di setiap halaman
- **Umpan Balik Visual**: Respons yang jelas saat berhasil mendapatkan flag

## Endpoint Vulnerable
Aplikasi memiliki beberapa endpoint yang rentan terhadap SQL Injection:

1. **Search Products** (`/search`)
   ```python
   query = f"SELECT * FROM products WHERE name LIKE '%{query}%' OR description LIKE '%{query}%'"
   ```

2. **User Profile** (`/user`)
   ```python
   query = f"SELECT * FROM users WHERE id={user_id}"
   ```

3. **Category Browsing** (`/category`)
   ```python
   query = f"SELECT * FROM products WHERE category='{category}'"
   ```

## Payload Umum
Beberapa payload SQL Injection yang dapat digunakan di berbagai endpoint:

- `' OR '1'='1`
- `1 OR 1=1`
- `' UNION SELECT 'admin', 'admin', 'admin', 'admin' --`
- `' AND 1=0 UNION SELECT 1,2,3,4 --`

## Mekanisme Flag
- Flag adalah static dengan nilai: `IDS{fd8840b063ec2c78cf9cdc7dec52f926}`
- Setelah berhasil mengeksploitasi salah satu endpoint, aplikasi akan menampilkan flag dalam UI yang jelas

## Petunjuk di Aplikasi
Aplikasi menyediakan petunjuk langsung di setiap endpoint:
- Petunjuk bahwa input tersebut rentan terhadap SQL injection
- Instruksi untuk mencoba berbagai payload
- Desain yang membantu peserta fokus pada tantangan

## Validasi Solusi
Untuk menyelesaikan challenge ini, peserta harus:
1. Mengakses halaman utama di http://localhost:5002
2. Mengidentifikasi endpoint yang rentan terhadap SQL Injection
3. Menggunakan payload SQL Injection pada parameter input
4. Mendapatkan flag yang ditampilkan setelah berhasil mengeksploitasi

## Pengetahuan yang Didapat
- Mengenali kerentanan SQL Injection pada berbagai endpoint aplikasi web
- Membuat payload SQL Injection untuk berbagai jenis query
- Memahami pentingnya sanitasi input dalam aplikasi web
- Mengetahui cara kerja mekanisme flag dinamis untuk mencegah cheating dalam CTF
- Mengenal prinsip-prinsip UI/UX dalam aplikasi keamanan siber

## Tips untuk Peserta
- Perhatikan bagaimana input dari berbagai form digunakan dalam query SQL
- Coba input yang tidak biasa seperti tanda kutip (') untuk melihat respons aplikasi
- Gunakan payload SQL Injection standar untuk setiap endpoint
- Pahami struktur query SQL yang rentan untuk membuat payload yang efektif
- Perhatikan petunjuk yang tersedia di antarmuka aplikasi
- Setelah mendapatkan flag, segera gunakan karena hanya valid selama 1 jam
- Coba semua endpoint yang tersedia untuk memahami berbagai jenis kerentanan