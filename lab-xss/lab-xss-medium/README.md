# Reflected XSS Lab - Medium Difficulty

## Deskripsi Singkat

Lab ini mendemonstrasikan kerentanan **Reflected XSS** dengan mekanisme **filter input dasar** yang dapat di-bypass. Filter memblokir tag dan event handler umum, namun memiliki celah yang memungkinkan eksekusi payload dengan teknik yang tepat.

## Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

1. **Bagaimana filter input XSS bekerja** dan keterbatasannya
2. **Teknik bypass filter dasar** menggunakan variasi encoding
3. **Konteks injeksi yang berbeda** dan cara memanfaatkannya
4. **Pentingnya output encoding** yang benar, bukan hanya input filtering

## Cara Menjalankan Lab

### Prasyarat

- Docker dan Docker Compose terinstall
- Flag generator script tersedia di `/home/labuser/tools/generate_flag.py`

### Menjalankan Lab

```bash
cd /home/labuser/tools/lab-xss/lab-xss-medium
docker-compose up -d
```

Lab akan berjalan pada **port acak** yang di-mapping oleh Docker. Untuk melihat port yang digunakan:

```bash
docker-compose ps
```

Atau akses langsung:
```bash
docker logs lab-xss-medium 2>&1 | grep "Access the lab"
```

### Menghentikan Lab

```bash
docker-compose down
```

## Hint Umum (Tanpa Spoiler)

1. **Perhatikan parameter apa yang diterima aplikasi** - coba berbagai input untuk melihat bagaimana data direfleksikan

2. **Amati bagaimana filter merespons input Anda** - filter ini tidak sempurna

3. **Eksplorasi konteks injeksi yang berbeda** - apakah input masuk ke atribut HTML? body HTML? atau JavaScript?

4. **Pertimbangkan teknik encoding alternatif** - terkadang karakter dapat direpresentasikan dengan berbagai cara

5. **Clue tersembunyi ada di halaman** - gunakan browser developer tools untuk menemukannya

## Flag Format

`IDS{************************}`

Flag hanya akan muncul melalui `alert()` JavaScript setelah payload berhasil dieksekusi.

---

**Disclaimer**: Lab ini untuk tujuan pembelajaran dan simulasi legal dalam lingkungan terkontrol.
