# SQL Injection Other Endpoints Lab

## Deskripsi
Lab ini menguji kemampuan peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection pada endpoint-endpoint selain login. Peserta harus mencari endpoint lain yang rentan terhadap SQL injection dan mengeksploitasinya untuk mendapatkan informasi sensitif.

## Tujuan
- Mengidentifikasi endpoint lain yang rentan terhadap SQL Injection
- Menggunakan teknik SQL injection untuk mengambil data sensitif
- Mendapatkan flag melalui eksploitasi endpoint yang ditemukan

## Teknologi
- Python Flask
- SQLite/MySQL
- Docker

## Cara Menjalankan
1. Pastikan Docker dan Docker Compose terinstal
2. Jalankan perintah: `docker-compose up -d`
3. Akses aplikasi di http://localhost:5002
4. Setelah menyelesaikan tantangan, flag akan ditampilkan

## Konfigurasi CTF
- Port: 5002
- Flag: `IDS{f0e9d8c7b6a5g4f3e2d1c0b9a8g7f6e5d}`
- Kategori: Web Exploitation
- Tingkat Kesulitan: Sedang

## Penyelesaian
Lihat file `solusi.txt` untuk panduan penyelesaian tantangan.