#!/bin/bash

# Script setup untuk Oracle Version SQL Injection Lab
echo "==========================================="
echo "Oracle Version SQL Injection Lab Setup"
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
if curl -s http://localhost:5005 > /dev/null; then
    echo "==========================================="
    echo "Aplikasi berhasil dijalankan!"
    echo "Akses: http://localhost:5005"
    echo "==========================================="
    echo ""
    echo "Cara menyelesaikan lab:"
    echo "1. Akses http://localhost:5005"
    echo "2. Gunakan fitur filter kategori produk"
    echo "3. Eksploitasi SQL injection UNION untuk mendapatkan versi database Oracle"
    echo "4. Coba payload seperti: ' UNION SELECT 1,2,3,4,version --"
    echo ""
    echo "Catatan: Tujuan lab ini adalah menampilkan string versi database Oracle"
    echo "==========================================="
else
    echo "Terjadi masalah saat menjalankan aplikasi. Silakan cek log dengan perintah:"
    echo "docker compose logs"
fi

# Menampilkan status container
echo ""
echo "Status container:"
docker ps --filter "name=oracle-version-lab-oracle-version-lab-1"