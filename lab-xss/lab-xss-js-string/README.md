# Reflected XSS Lab - JavaScript String Context (Medium)

## Deskripsi Singkat

Lab ini mendemonstrasikan kerentanan **Reflected XSS dalam konteks string JavaScript**. Berbeda dengan XSS pada HTML biasa, kerentanan ini terjadi ketika input pengguna direfleksikan ke dalam literal string JavaScript, memerlukan teknik khusus untuk keluar dari konteks string.

## Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

1. **Konteks injeksi yang berbeda** - HTML vs JavaScript string
2. **Cara keluar dari string literal** - single quote, double quote, backtick
3. **Terminasi statement JavaScript** - menggunakan titik koma, komentar
4. **Encoding dalam konteks JavaScript** - bagaimana karakter di-encode

## Cara Menjalankan Lab

### Prasyarat

- Docker dan Docker Compose terinstall
- Flag generator script tersedia di `/home/labuser/tools/generate_flag.py`

### Menjalankan Lab

```bash
cd /home/labuser/tools/lab-xss/lab-xss-js-string
docker compose up -d
```

Lab akan berjalan pada **port acak**. Untuk melihat port yang digunakan:

```bash
docker compose ps
```

## Hint Umum (Tanpa Spoiler)

1. **Perhatikan konteks di mana input Anda muncul** - apakah di dalam tanda kutip?

2. **Eksplorasi berbagai jenis kutip** - single quote ('), double quote ("), backtick (`)

3. **Pahami struktur sintaks JavaScript** - bagaimana mengakhiri string dan statement?

4. **Perhatikan karakter apa yang di-escape** oleh aplikasi

5. **Coba berbagai parameter URL** - parameter apa yang tersedia?

6. **Gunakan browser developer tools** - periksa source JavaScript dan bagaimana data direfleksikan

## Flag Format

`IDS{************************}`

Flag hanya akan muncul melalui `alert()` JavaScript setelah payload berhasil dieksekusi.

---

**Disclaimer**: Lab ini untuk tujuan pembelajaran dan simulasi legal dalam lingkungan terkontrol.
