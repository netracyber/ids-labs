# Reflected XSS Lab - Event Handler Attribute (Medium)

## Deskripsi Singkat

Lab ini mendemonstrasikan kerentanan **Reflected XSS melalui atribut event handler**. Kerentanan terjadi ketika input pengguna direfleksikan ke dalam nilai atribut event handler HTML seperti `onerror`, `onload`, `onclick`, dll., yang memerlukan teknik khusus untuk keluar dari konteks atribut.

## Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

1. **Konteks atribut HTML** - bagaimana data direfleksikan dalam atribut
2. **Event handler attributes** - `onerror`, `onload`, `onclick`, dll.
3. **Cara keluar dari atribut** - menutup kutip dan tag
4. **Encoding dalam atribut** - HTML entity encoding vs URL encoding

## Cara Menjalankan Lab

### Prasyarat

- Docker dan Docker Compose terinstall
- Flag generator script tersedia di `/home/labuser/tools/generate_flag.py`

### Menjalankan Lab

```bash
cd /home/labuser/tools/lab-xss/lab-xss-event-handler
docker compose up -d
```

Lab akan berjalan pada **port acak**. Untuk melihat port yang digunakan:

```bash
docker compose ps
```

## Hint Umum (Tanpa Spoiler)

1. **Perhatikan konteks atribut** - input Anda berada di dalam nilai atribut HTML

2. **Identifikasi jenis kutip yang digunakan** - single quote (') atau double quote (")

3. **Event handler berisi kode JavaScript** - apa yang valid di dalam event handler?

4. **Anda perlu keluar dari atribut sebelum menyisipkan script** - bagaimana cara menutup atribut?

5. **Perhatikan karakter apa yang di-encode** oleh aplikasi

6. **Coba berbagai parameter URL** - beberapa parameter mungkin memiliki validasi berbeda

## Flag Format

`IDS{************************}`

Flag hanya akan muncul melalui `alert()` JavaScript setelah payload berhasil dieksekusi.

---

**Disclaimer**: Lab ini untuk tujuan pembelajaran dan simulasi legal dalam lingkungan terkontrol.
