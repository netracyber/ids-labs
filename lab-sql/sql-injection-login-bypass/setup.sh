#!/bin/bash

# Script setup untuk SQL Injection Login Bypass CTF Lab
# Created for IDS CyberSec Academy

echo "==========================================="
echo "SQL Injection Login Bypass CTF Lab Setup"
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
if curl -s http://localhost:5001 > /dev/null; then
    echo "==========================================="
    echo "Aplikasi berhasil dijalankan!"
    echo "Akses: http://localhost:5001"
    echo "==========================================="
    echo ""
    echo "Fitur aplikasi:"
    echo "- UI modern dan ramah pengguna"
    echo "- Petunjuk built-in untuk membantu peserta"
    echo "- Respons visual saat login berhasil/gagal"
    echo ""
    echo "Cara mengeksploitasi SQL Injection:"
    echo "1. Buka http://localhost:5001 di browser"
    echo "2. Perhatikan petunjuk yang tersedia di halaman login"
    echo "3. Pada form login, masukkan payload SQL Injection:"
    echo "   Username: admin"
    echo "   Password: ' OR '1'='1"
    echo "4. Submit form untuk mendapatkan flag"
    echo ""
    echo "Catatan: Flag bersifat statis - tidak berubah sepanjang waktu"
    echo "==========================================="
else
    echo "Terjadi masalah saat menjalankan aplikasi. Silakan cek log dengan perintah:"
    echo "docker compose logs"
fi

# Menampilkan status container
echo ""
echo "Status container:"
docker ps --filter "name=sql-injection-login-bypass-sqli-lab-1"