# SQL Injection Login Bypass Lab

## Deskripsi
Lab ini dirancang untuk menguji kemampuan peserta dalam mengidentifikasi dan mengeksploitasi kerentanan SQL Injection pada form login. Peserta harus dapat melewati otentikasi dengan menggunakan teknik SQL injection.

## Tujuan
- Mengidentifikasi kerentanan SQL Injection pada form login
- Menggunakan payload SQL injection untuk melewati otentikasi
- Mendapatkan flag setelah berhasil login

## Teknologi
- Python Flask
- SQLite/MySQL
- Docker

## Cara Menjalankan
1. Pastikan Docker dan Docker Compose terinstal
2. Jalankan perintah: `docker-compose up -d`
3. Akses aplikasi di http://localhost:5001
4. Setelah menyelesaikan tantangan, flag akan ditampilkan

## Konfigurasi CTF
- Port: 5001
- Flag: `IDS{a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p}`
- Kategori: Web Exploitation
- Tingkat Kesulitan: Mudah

## Penyelesaian
Lihat file `solusi.txt` untuk panduan penyelesaian tantangan.