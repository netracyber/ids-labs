#!/bin/bash

# Script setup untuk SQL Injection CTF Lab (Other Endpoints)
# Created for IDS CyberSec Academy

echo "==========================================="
echo "SQL Injection CTF Lab (Other Endpoints) Setup"
echo "==========================================="

# Mengecek apakah Docker terinstal
if ! [ -x "$(command -v docker)" ]; then
  echo "Error: Docker tidak ditemukan. Silakan instal Docker terlebih dahulu." >&2
  exit 1
fi

# Mengecek apakah docker compose terinstal
if ! [ -x "$(command -v docker compose)" ]; then
  echo "Error: Docker Compose tidak ditemukan. Silakan instal Docker Compose terlebih dahulu." >&2
  exit 1
fi

echo "Semua dependensi ditemukan. Melanjutkan setup..."

# Build dan jalankan aplikasi
echo "Membangun dan menjalankan aplikasi..."
docker compose up -d --build

# Tunggu beberapa detik agar aplikasi siap
echo "Menunggu aplikasi siap..."
sleep 10

# Cek apakah aplikasi berjalan
if curl -s http://localhost:5002 > /dev/null; then
    echo "==========================================="
    echo "Aplikasi berhasil dijalankan!"
    echo "Akses: http://localhost:5002"
    echo "==========================================="
    echo ""
    echo "Fitur aplikasi:"
    echo "- UI modern dan ramah pengguna"
    echo "- Banyak endpoint yang rentan terhadap SQL injection"
    echo "- Petunjuk built-in untuk membantu peserta"
    echo "- Respons visual saat berhasil mendapatkan flag"
    echo ""
    echo "Cara mengeksploitasi SQL Injection:"
    echo "1. Buka http://localhost:5002 di browser"
    echo "2. Eksplorasi berbagai endpoint yang tersedia"
    echo "3. Coba endpoint /search, /user, dan /category"
    echo "4. Gunakan payload SQL Injection pada parameter input"
    echo "   Contoh: /search?q=' OR '1'='1"
    echo "           /user?id=1 OR 1=1"
    echo "           /category?cat=Electronics' OR '1'='1"
    echo "5. Dapatkan flag setelah berhasil mengeksploitasi"
    echo ""
    echo "Catatan: Flag adalah static"
    echo "==========================================="
else
    echo "Terjadi masalah saat menjalankan aplikasi. Silakan cek log dengan perintah:"
    echo "docker compose logs"
fi

# Menampilkan status container
echo ""
echo "Status container:"
docker ps --filter "name=sqli-other-endpoints-sqli-other-endpoints-1"