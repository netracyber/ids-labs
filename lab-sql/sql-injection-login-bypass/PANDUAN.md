# Panduan Penggunaan SQL Injection Login Bypass Lab

## Gambaran Umum
Lab ini dirancang untuk melatih peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection pada aplikasi web. Aplikasi ini sengaja dibuat rentan terhadap SQL Injection tipe login bypass dengan UI modern dan petunjuk built-in.

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
      │ auth bypass
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
- **Petunjuk Built-in**: Informasi dan petunjuk langsung di halaman
- **Umpan Balik Visual**: Respons yang jelas saat login berhasil atau gagal

## Cara Kerja SQL Injection
Aplikasi menggunakan query yang rentan terhadap SQL Injection:
```python
query = f"SELECT * FROM users WHERE username='{username}' AND password='{password}'"
```

Query ini tidak di-sanitize, memungkinkan peserta untuk memanipulasi query SQL dengan input mereka sendiri.

## Payload Umum
Beberapa payload SQL Injection yang dapat digunakan:
- `' OR '1'='1`
- `' UNION SELECT 'admin', 'admin' --`
- `' OR 1=1 --`

## Mekanisme Flag Statis
- Flag bersifat statis dan tidak berubah sepanjang waktu
- Format flag: `IDS{...}`
- Setelah login berhasil melalui SQL injection, aplikasi akan menampilkan flag dalam UI yang jelas
- Flag tidak pernah kedaluwarsa

## Petunjuk di Aplikasi
Aplikasi menyediakan petunjuk langsung di halaman login:
- Petunjuk bahwa form ini rentan terhadap SQL injection
- Instruksi untuk mencoba bypass otentikasi
- Desain yang membantu peserta fokus pada tantangan

## Validasi Solusi
Untuk menyelesaikan challenge ini, peserta harus:
1. Mengakses halaman login di http://localhost:5001
2. Mengidentifikasi kerentanan SQL Injection
3. Menggunakan payload SQL Injection untuk melewati otentikasi
4. Mendapatkan flag yang ditampilkan setelah login berhasil

## Pengetahuan yang Didapat
- Mengenali kerentanan SQL Injection pada aplikasi web
- Membuat payload SQL Injection untuk login bypass
- Memahami pentingnya sanitasi input dalam aplikasi web
- Mengetahui cara kerja mekanisme flag dinamis untuk mencegah cheating dalam CTF
- Mengenal prinsip-prinsip UI/UX dalam aplikasi keamanan siber

## Tips untuk Peserta
- Perhatikan bagaimana input dari form login digunakan dalam query SQL
- Coba input yang tidak biasa seperti tanda kutip (') untuk melihat respons aplikasi
- Gunakan payload SQL Injection standar untuk login bypass
- Pahami struktur query SQL yang rentan untuk membuat payload yang efektif
- Perhatikan petunjuk yang tersedia di antarmuka aplikasi
- Setelah mendapatkan flag, Anda dapat menggunakannya sepanjang waktu