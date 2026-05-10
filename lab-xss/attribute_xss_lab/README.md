# Attribute XSS Lab - Reflected XSS in HTML Attribute (Easy)

## 📋 Deskripsi Lab

Selamat datang di **Attribute XSS Lab**! Lab ini mensimulasikan sebuah formulir pencarian yang memiliki kerentanan **Reflected Cross-Site Scripting (XSS)** melalui **HTML attribute context**.

Aplikasi "SecureForm Pro" menampilkan input pengguna kembali di dalam atribut HTML (`value=""` attribute) tanpa sanitasi yang tepat untuk konteks attribute.

## 🎯 Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

- Konsep **Reflected XSS dalam HTML attribute context**
- Perbedaan antara injeksi di HTML body vs HTML attribute
- Cara keluar dari attribute context menggunakan teknik tertentu
- Pentingnya **attribute encoding** (bukan hanya HTML encoding)
- Teknik menggunakan event handler untuk eksekusi JavaScript

## 🚀 Cara Menjalankan Lab

### Menjalankan Lab Ini:

```bash
cd /home/labuser/tools/lab-xss
docker compose up attribute-xss-lab -d
```

Lihat port yang digunakan:
```bash
docker port xss-lab-attribute
```

Akses aplikasi di browser: `http://localhost:<port>`

## 🛑 Cara Menghentikan Lab

```bash
docker compose down attribute-xss-lab
```

## 🎮 Cara Bermain

1. Buka aplikasi di browser
2. Coba masukkan query pencarian sederhana
3. Perhatikan URL dan parameter yang digunakan
4. Analisis source code untuk menemukan titik injeksi
5. Pahami konteks injeksi (HTML attribute)
6. Buat payload XSS yang sesuai dengan attribute context
7. Eksekusi payload untuk mendapatkan flag

## 💡 Hint (Tanpa Spoiler)

### Hint 1 - Parameter Discovery
Perhatikan URL saat Anda melakukan pencarian. Parameter apa yang digunakan?

### Hint 2 - Context Analysis
Coba test dengan input: `test` dan lakukan view-source. Di mana input Anda muncul?

### Hint 3 - Attribute Understanding
Input Anda muncul di dalam atribut HTML. Atribut apa yang vulnerable?

### Hint 4 - Breaking Out
Untuk mengeksekusi JavaScript dalam attribute, Anda harus "keluar" dari attribute terlebih dahulu. Bagaimana cara keluar dari quoted attribute?

### Hint 5 - Event Handlers
Setelah keluar dari attribute, Anda perlu event handler JavaScript. Apa event handler yang bisa trigger secara otomatis?

### Hint 6 - Clue Tersembunyi
Refresh halaman beberapa kali untuk melihat petunjuk berbeda di source code.

## 📚 Konsep Teknis

### Apa itu HTML Attribute Context?

HTML attribute context terjadi ketika input pengguna ditempatkan di dalam nilai atribut HTML:

```html
<input value="USER_INPUT_HERE">
```

### Mengapa Ini Berbeda dari HTML Body?

- **HTML Body**: `<div>USER_INPUT</div>` → Bisa langsung inject `<script>`
- **HTML Attribute**: `<input value="USER_INPUT">` → Perlu keluar dari attribute dulu

### Vector Serangan pada Lab Ini

```
GET /?search=<payload_attribute_xss>
```

Parameter `search` direfleksikan di dalam `value=""` attribute.

### Mengapa Ini Vulnerable?

```php
<!-- VULNERABLE CODE -->
<input value="<?php echo $search_query; ?>">
```

Kode di atas langsung menampilkan input pengguna tanpa encoding untuk attribute context. Input bisa keluar dari quoted string dan menyisipkan event handler.

## 🔒 Solusi (Spoiler Warning!)

<details>
<summary>Klik untuk melihat solusi</summary>

### Payload Dasar untuk Attribute XSS

```
?search=" onload="alert(1)
```

### Penjelasan Payload:

1. `"` - Menutup value attribute yang terbuka
2. `onload=` - Menambahkan event handler
3. `"alert(1)"` - Memanggil alert ditutup dengan quote

### Hasil Rendering:

```html
<input value="" onload="alert(1)"">
```

### Payload untuk Mendapatkan Flag

```
?search=" onload="alert(flagData)
```

Atau menggunakan fungsi validasi:

```
?search=" onload="window.validateFlag(flagData)
```

### Penjelasan Teknis

1. Input `search` direfleksikan dalam `value=""` attribute
2. Payload `"` menutup attribute value yang terbuka
3. `onload=` menyisipkan event handler JavaScript
4. Event handler akan dieksekusi saat elemen dimuat
5. JavaScript mengakses variabel `flagData` yang berisi flag

### Event Handlers yang Bisa Digunakan:

- `onload` - Saat elemen dimuat
- `onfocus` - Saat elemen mendapatkan fokus
- `onmouseover` - Saat mouse di atas elemen
- `onmouseenter` - Saat mouse masuk ke elemen

### Perbaikan yang Disarankan

```php
<!-- SECURE CODE -->
<input value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>">
```

`ENT_QUOTES` akan meng-encode single dan double quotes.

</details>

## 📊 Checklist Pengerjaan

- [ ] Menemukan parameter vulnerable
- [ ] Mengidentifikasi bahwa injeksi di HTML attribute
- [ ] Memahami cara keluar dari quoted attribute
- [ ] Menemukan event handler yang tepat
- [ ] Membuat payload XSS untuk attribute context
- [ ] Mengeksekusi payload untuk mendapatkan flag
- [ ] Memahami perbedaan attribute vs body encoding

## ⚠️ Catatan Penting

- Lab ini untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol
- Teknik yang dipelajari **jangan digunakan untuk tujuan ilegal**
- Fokus pada pemahaman konsep keamanan, bukan sekadar mendapatkan flag
- Dalam aplikasi nyata, selalu gunakan **proper output encoding** sesuai konteks

## 🔖 Level & Teknik

- **Level**: Easy
- **Teknik**: Reflected XSS via query parameter (HTML attribute context)
- **Konteks**: HTML attribute injection
- **Parameter**: `search`
- **Filter**: None (no CSP, no WAF, no input sanitization)

## 🔗 Perbedaan dengan Lab Lain

| Lab | Konteks Injeksi | Parameter |
|-----|----------------|-----------|
| Search Query Lab | HTML body | `q` |
| **Attribute Lab** | **HTML attribute** | `search` |

---

**Author**: IDS – CyberSec Academy Lab Authoring Guideline
**Version**: 1.0
**Last Updated**: 2025
