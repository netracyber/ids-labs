# Search Query XSS Lab - Reflected XSS (Easy)

## 📋 Deskripsi Lab

Selamat datang di **Search Query XSS Lab**! Lab ini mensimulasikan sebuah aplikasi pencarian sederhana yang memiliki kerentanan **Reflected Cross-Site Scripting (XSS)** melalui query parameter.

Aplikasi "QuickSearch Pro" menampilkan hasil pencarian dengan merfleksikan input pengguna kembali ke halaman tanpa sanitasi yang tepat.

## 🎯 Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

- Konsep dasar **Reflected XSS** melalui query parameter
- Cara mengidentifikasi parameter yang vulnerable
- Bagaimana input pengguna dapat dieksekusi sebagai JavaScript
- Pentingnya **output encoding** dan **input sanitization**
- Teknik dasar penyusunan payload XSS

## 🚀 Cara Menjalankan Lab

### Opsi 1: Menjalankan Lab Ini Saja

```bash
cd /home/labuser/tools/lab-xss
docker-compose up search-query-xss-lab -d
```

Lihat port yang digunakan:
```bash
docker port xss-lab-search-query
```

Akses aplikasi di browser: `http://localhost:<port>`

### Opsi 2: Menjalankan Semua Lab

```bash
cd /home/labuser/tools/lab-xss
docker-compose up -d
```

Lab ini akan tersedia di port yang ditentukan (cek dengan `docker-compose ps`).

## 🛑 Cara Menghentikan Lab

```bash
docker-compose down search-query-xss-lab
```

Atau untuk menghentikan semua lab:
```bash
docker-compose down
```

## 🎮 Cara Bermain

1. Buka aplikasi di browser
2. Coba masukkan query pencarian sederhana (misal: `test`)
3. Perhatikan bagaimana input Anda ditampilkan kembali
4. Analisis source code untuk menemukan titik injeksi
5. Buat payload XSS yang sesuai dengan konteks injeksi
6. Eksekusi payload untuk mendapatkan flag

## 💡 Hint (Tanpa Spoiler)

### Hint 1 - Parameter Discovery
Perhatikan URL saat Anda melakukan pencarian. Parameter apa yang digunakan?

### Hint 2 - Input Reflection
Coba test dengan input sederhana dan lihat di mana input tersebut muncul di halaman.

### Hint 3 - Source Code Analysis
View-source dapat membantu Anda memahami bagaimana input diproses.

### Hint 4 - Context Matters
Perhatikan konteks di mana input direfleksikan - apakah di dalam tag HTML, attribute, atau JavaScript?

### Hint 5 - Clue Tersembunyi
Refresh halaman beberapa kali untuk melihat petunjuk berbeda. Ada sesuatu yang tersembunyi di source code.

### Hint 6 - Validation
Flag hanya akan muncul jika JavaScript berhasil dieksekusi.

## 📚 Konsep Teknis

### Apa itu Reflected XSS?

Reflected XSS terjadi ketika aplikasi web menerima input dari pengunakan (biasanya melalui URL) dan langsung menampilkannya kembali tanpa sanitasi. Input "direfleksikan" kembali ke respons server.

### Vector Serangan pada Lab Ini

```
GET /?q=<payload_xss>
```

Parameter `q` direfleksikan kembali ke halaman tanpa encoding.

### Mengapa Ini Vulnerable?

```php
<!-- VULNERABLE CODE -->
<span id="query-display"><?php echo $search_query; ?></span>
```

Kode di atas langsung menampilkan input pengunakan tanpa `htmlspecialchars()` atau fungsi encoding lainnya.

## 🔒 Solusi (Spoiler Warning!)

<details>
<summary>Klik untuk melihat solusi</summary>

### Payload Dasar

```
?q=<script>alert(1)</script>
```

### Payload untuk Mendapatkan Flag

```
?q=<script>alert(flagData)</script>
```

Atau dengan memanggil fungsi validasi:

```
?q=<script>window.validateFlag(flagData)</script>
```

### Penjelasan

1. Parameter `q` direfleksikan di dalam elemen `<span id="query-display">`
2. Karena tidak ada encoding, tag `<script>` akan dieksekusi oleh browser
3. JavaScript dapat mengakses variabel `flagData` yang berisi flag
4. Flag ditampilkan melalui `alert()` saat payload berhasil dieksekusi

### Perbaikan yang Disarankan

```php
<!-- SECURE CODE -->
<span id="query-display"><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></span>
```

</details>

## 📊 Checklist Pengerjaan

- [ ] Menemukan parameter vulnerable
- [ ] Mengidentifikasi konteks injeksi
- [ ] Membuat payload XSS dasar
- [ ] Mengeksekusi payload untuk mendapatkan flag
- [ ] Memahami mengapa kerentanan terjadi
- [ ] Mengetahui cara memperbaiki kerentanan

## ⚠️ Catatan Penting

- Lab ini untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol
- Teknik yang dipelajari **jangan digunakan untuk tujuan ilegal**
- Fokus pada pemahaman konsep keamanan, bukan sekadar mendapatkan flag
- Dalam aplikasi nyata, selalu gunakan **output encoding** dan **Content Security Policy (CSP)**

## 🔖 Level & Teknik

- **Level**: Easy
- **Teknik**: Reflected XSS via query parameter
- **Konteks**: HTML body injection
- **Filter**: None (no CSP, no WAF, no input sanitization)

---

**Author**: IDS – CyberSec Academy Lab Authoring Guideline
**Version**: 1.0
**Last Updated**: 2025
