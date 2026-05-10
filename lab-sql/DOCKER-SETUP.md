# SQL Injection Labs - Docker Setup

## Overview
Semua lab SQL Injection dapat dijalankan menggunakan Docker dan Docker Compose. Setiap lab berjalan dalam container terpisah dengan port yang berbeda.

## Prerequisites
- Docker
- Docker Compose (terintegrasi dalam Docker versi terbaru)

## Port Mapping

| Lab | Container Port | Host Port | URL |
|-----|----------------|-----------|-----|
| SQL Injection Login Bypass | 5000 | 5001 | http://localhost:5001 |
| SQL Injection Other Endpoints | 5000 | 5002 | http://localhost:5002 |
| SQL Injection Hidden Data | 5000 | 5003 | http://localhost:5003 |
| Oracle Version Lab | 5005 | 5005 | http://localhost:5005 |
| SQL Injection DB Version | 5000 | 5006 | http://localhost:5006 |
| SQL Injection Oracle Enum | 5000 | 5007 | http://localhost:5007 |
| SQL Injection Lab (Complete) | 6003 | 6003 | http://localhost:6003 |
| Oracle Database (Lab 6) | 1521 | 1527 | localhost:1527 |

## Quick Start

### Option 1: Menggunakan Setup Script
```bash
chmod +x run-all.sh
./run-all.sh
```

### Option 2: Manual Setup

1. **Build semua images:**
```bash
docker compose build
```

2. **Jalankan semua containers:**
```bash
docker compose up -d
```

3. **Cek status containers:**
```bash
docker compose ps
```

## Perintah Docker Compose

### Lihat logs semua container:
```bash
docker compose logs -f
```

### Lihat logs container tertentu:
```bash
docker compose logs -f sqli-login-bypass
docker compose logs -f sqli-oracle-enum
```

### Stop semua container:
```bash
docker compose down
```

### Restart container tertentu:
```bash
docker compose restart sqli-login-bypass
```

### Rebuild container setelah perubahan:
```bash
docker compose up -d --build sqli-login-bypass
```

## Menjalankan Lab Individual

Jika ingin menjalankan satu lab saja, masuk ke direktori lab tersebut:

```bash
cd sql-injection-login-bypass
docker compose up -d --build
```

## Troubleshooting

### Permission Denied
Jika mendapat error "permission denied while trying to connect to the Docker daemon socket":

```bash
# Tambahkan user ke docker group
sudo usermod -aG docker $USER

# Logout dan login kembali agar group aktif
# Atau gunakan:
newgrp docker
```

### Port Already in Use
Jika port sudah digunakan, ubah port mapping di `docker-compose.yml`:

```yaml
ports:
  - "5001:5000"  # Ubah 5001 ke port lain yang tersedia
```

### Container Gagal Start
Cek logs untuk melihat error:
```bash
docker compose logs [service-name]
```

### Oracle Database Tidak Start
Oracle database membutuhkan waktu untuk start. Tunggu healthcheck selesai:

```bash
docker compose logs oracle-db
```

## Lab Descriptions

### 1. SQL Injection Login Bypass (Port 5001)
Lab sederhana untuk bypass login menggunakan SQL injection.

### 2. SQL Injection Other Endpoints (Port 5002)
Lab dengan endpoint selain login yang vulnerable.

### 3. SQL Injection Hidden Data (Port 5003)
Lab untuk meretrieve data tersembunyi menggunakan SQL injection.

### 4. Oracle Version Lab (Port 5005)
Lab khusus untuk query version pada Oracle database.

### 5. SQL Injection DB Version (Port 5006)
Lab untuk mengetahui tipe dan versi database.

### 6. SQL Injection Oracle Enum (Port 5007)
Lab untuk enumeration pada Oracle database dengan Oracle XE container.

### 7. SQL Injection Lab (Port 6003)
Lab lengkap dengan sistem login, dashboard, dan multiple users.

## Stopping All Labs

```bash
# Stop semua container
docker compose down

# Stop dan hapus volumes
docker compose down -v

# Stop dan hapus images juga
docker compose down --rmi all
```

## Network
Semua container berada dalam network yang sama dan dapat komunikasi antar container. Nama service dapat digunakan sebagai hostname (contoh: `oracle-db` untuk koneksi ke Oracle database).
