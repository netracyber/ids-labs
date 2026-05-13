# LFI Lab - Medium-Hard (Standalone)

## Deskripsi Singkat

Lab ini mendemonstrasikan kerentanan **Local File Inclusion (LFI) / Path Traversal** pada fitur "viewer" internal.
Aplikasi terlihat memiliki *allowlist* file dan filter traversal sederhana, namun ada celah pada proses normalisasi/validasi path, terutama saat path ter-encode lebih dari sekali.

## Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

1. Bagaimana aplikasi sering melakukan *path allowlist* yang terlihat aman
2. Mengapa *normalization mismatch* (validasi vs penggunaan) bisa berbahaya
3. Teknik bypass filter traversal sederhana (tanpa menambahkan vuln lain)

## Cara Menjalankan Lab

### Prasyarat

- Docker dan Docker Compose terinstall
- Flag generator script tersedia di `/home/labuser/tools/generate_flag.py`

### Menjalankan Lab

```bash
cd /home/labuser/tools/lab-xss/lab-xss-lfi-medium-hard
docker-compose up -d
```

Akses lab:

- `http://localhost:8048/`

### Menghentikan Lab

```bash
docker-compose down
```

## Flag Format

`IDS{************************}`

Flag hanya akan muncul melalui `alert()` JavaScript setelah exploit LFI berhasil.

---

**Disclaimer**: Lab ini untuk tujuan pembelajaran dan simulasi legal dalam lingkungan terkontrol.
