# SQL Injection Hidden Data Lab

## Deskripsi
Lab ini menguji kemampuan peserta dalam mengungkap data tersembunyi melalui teknik SQL Injection. Peserta harus menggunakan UNION-based SQL injection untuk mengambil data dari tabel yang tidak terpublikasikan.

## Tujuan
- Mengidentifikasi parameter yang rentan terhadap SQL Injection
- Menggunakan teknik UNION-based injection untuk mengambil data tersembunyi
- Mendapatkan flag dari tabel yang tidak terpublikasikan

## Teknologi
- Python Flask
- SQLite/MySQL
- Docker

## Cara Menjalankan
1. Pastikan Docker dan Docker Compose terinstal
2. Jalankan perintah: `docker-compose up -d`
3. Akses aplikasi di http://localhost:5003
4. Setelah menyelesaikan tantangan, flag akan ditampilkan

## Konfigurasi CTF
- Port: 5003
- Flag: `IDS{1365f3f91559b9d4ddd073b51b156e15}`
- Kategori: Web Exploitation
- Tingkat Kesulitan: Sedang

## Penyelesaian
Lihat file `solusi.txt` untuk panduan penyelesaian tantangan.