# Oracle Version Lab

## Deskripsi
Lab ini menguji kemampuan peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection pada database Oracle. Peserta harus menggunakan teknik khusus Oracle untuk mendapatkan informasi versi dan data sensitif lainnya.

## Tujuan
- Mengidentifikasi bahwa aplikasi menggunakan database Oracle
- Menggunakan payload khusus Oracle untuk SQL injection
- Mendapatkan informasi versi Oracle dan data sensitif
- Mendapatkan flag melalui eksploitasi Oracle

## Teknologi
- Python Flask
- Oracle Database
- Docker

## Cara Menjalankan
1. Pastikan Docker dan Docker Compose terinstal
2. Jalankan perintah: `docker-compose up -d`
3. Akses aplikasi di http://localhost:5005
4. Setelah menyelesaikan tantangan, flag akan ditampilkan

## Konfigurasi CTF
- Port: 5005
- Flag: `IDS{c5b4a3f2e1d0g9h8i7j6k5l4m3n2o1p0q}`
- Kategori: Web Exploitation
- Tingkat Kesulitan: Sulit

## Penyelesaian
Lihat file `solusi.txt` untuk panduan penyelesaian tantangan.