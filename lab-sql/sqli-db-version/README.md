# SQL Injection DB Version Lab

## Deskripsi
Lab ini menguji kemampuan peserta dalam mengidentifikasi versi database melalui teknik SQL Injection. Peserta harus menggunakan berbagai teknik untuk mengungkap informasi versi database dan mengeksploitasinya lebih lanjut.

## Tujuan
- Mengidentifikasi parameter yang rentan terhadap SQL Injection
- Menggunakan teknik error-based atau UNION-based untuk mendapatkan informasi versi database
- Mengeksploitasi informasi versi untuk mendapatkan data sensitif
- Mendapatkan flag melalui eksploitasi versi database

## Teknologi
- Python Flask
- SQLite/MySQL/PostgreSQL
- Docker

## Cara Menjalankan
1. Pastikan Docker dan Docker Compose terinstal
2. Jalankan perintah: `docker-compose up -d`
3. Akses aplikasi di http://localhost:5006
4. Setelah menyelesaikan tantangan, flag akan ditampilkan

## Konfigurasi CTF
- Port: 5006
- Flag: `IDS{d9e8f7g6h5i4j3k2l1m0n9o8p7q6r5s4}`
- Kategori: Web Exploitation
- Tingkat Kesulitan: Sedang

## Penyelesaian
Lihat file `solusi.txt` untuk panduan penyelesaian tantangan.