# DOM-based XSS Lab - Medium Difficulty

## Deskripsi Singkat

Lab ini mendemonstrasikan kerentanan **DOM-based XSS** di mana data dari URL (melalui `document.location`) diambil dan dimasukkan ke dalam DOM menggunakan metode yang tidak aman. Tidak ada refleksi server-side - kerentanan sepenuhnya berada di sisi klien.

## Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

1. **Apa itu DOM-based XSS** dan bagaimana berbeda dari reflected XSS
2. **Sumber DOM** (sources) seperti `document.location`, `document.URL`, `window.location`
3. **Sink DOM** (sinks) berbahaya seperti `innerHTML`, `eval()`, `document.write()`
4. **Bagaimana memvalidasi input di sisi klien** dengan benar

## Cara Menjalankan Lab

### Prasyarat

- Docker dan Docker Compose terinstall
- Flag generator script tersedia di `/home/labuser/tools/generate_flag.py`

### Menjalankan Lab

```bash
cd /home/labuser/tools/lab-xss/lab-xss-dom-location
docker compose up -d
```

Lab akan berjalan pada **port acak**. Untuk melihat port yang digunakan:

```bash
docker compose ps
```

Atau akses langsung:
```bash
docker logs lab-xss-dom-location 2>&1 | grep "Access the lab"
```

### Menghentikan Lab

```bash
docker compose down
```

## Hint Umum (Tanpa Spoiler)

1. **Perhatikan URL dengan cermat** - parameter URL apa yang tersedia?

2. **Gunakan Developer Tools browser** - periksa JavaScript yang berjalan di halaman

3. **Pahami alur data** - dari mana data berasal dan ke mana ia pergi?

4. **Eksplorasi berbagai bagian URL**:
   - Query string (`?name=value`)
   - Hash fragment (`#section`)
   - Path pathname

5. **Beberapa fungsi JavaScript lebih berbahaya dari yang lain** - `innerHTML` vs `textContent`

6. **Validasi klien dapat di-bypass** - karena JavaScript dapat dimodifikasi

## Flag Format

`IDS{************************}`

Flag hanya akan muncul melalui `alert()` JavaScript setelah payload berhasil dieksekusi.

---

**Disclaimer**: Lab ini untuk tujuan pembelajaran dan simulasi legal dalam lingkungan terkontrol.
