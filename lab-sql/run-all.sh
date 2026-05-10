#!/bin/bash

# SQL Injection Labs - Docker Setup Script
# Script ini untuk menjalankan semua lab SQL Injection dalam Docker container

echo "=========================================="
echo "   SQL Injection Labs - Docker Setup"
echo "=========================================="
echo ""

# Cek apakah Docker terinstall
if ! command -v docker &> /dev/null; then
    echo "[ERROR] Docker tidak terinstall. Silakan install Docker terlebih dahulu."
    exit 1
fi

# Setup Docker group jika belum
echo "[1/5] Setting up Docker permissions..."
sudo usermod -aG docker $USER 2>/dev/null || echo "Note: Anda mungkin perlu logout dan login kembali agar group docker aktif"

# Build semua images
echo ""
echo "[2/5] Building Docker images..."
docker compose build

# Jalankan semua container
echo ""
echo "[3/5] Starting all containers..."
docker compose up -d

# Tampilkan status container
echo ""
echo "[4/5] Container status:"
docker compose ps

echo ""
echo "[5/5] Access URLs:"
echo "=========================================="
echo "All labs are now running in Docker!"
echo ""
echo "Available Labs:"
echo "  1. SQL Injection Login Bypass:  http://localhost:5001"
echo "  2. SQL Injection Other Endpoints: http://localhost:5002"
echo "  3. SQL Injection Hidden Data:    http://localhost:5003"
echo "  4. Oracle Version Lab:           http://localhost:5005"
echo "  5. SQL Injection DB Version:     http://localhost:5006"
echo "  6. SQL Injection Oracle Enum:    http://localhost:5007"
echo "  7. SQL Injection Lab:            http://localhost:6003"
echo ""
echo "Database:"
echo "  Oracle DB (for lab 6):           localhost:1527"
echo ""
echo "Commands:"
echo "  - Stop all:    docker compose down"
echo "  - View logs:   docker compose logs -f [service-name]"
echo "  - Restart:     docker compose restart [service-name]"
echo "=========================================="
