# Document.write XSS Lab - Reflected XSS via document.write() (Easy)

## 📋 Deskripsi Lab

Selamat datang di **Document.write XSS Lab**! Lab ini mensimulasikan sebuah aplikasi dynamic rendering yang memiliki kerentanan **Reflected Cross-Site Scripting (XSS)** melalui penggunaan fungsi **`document.write()`**.

Aplikasi "DynamicPage Pro" merender input pengguna menggunakan fungsi `document.write()` tanpa sanitasi yang tepat.

## 🎯 Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

- Konsep **Reflected XSS melalui document.write()**
- Bagaimana `document.write()` dapat mengeksekusi JavaScript
- Perbedaan document.write() dengan metode DOM manipulation lainnya
- Risiko keamanan dari penggunaan document.write()
- Pentingnya validasi input saat menggunakan DOM manipulation

## 🚀 Cara Menjalankan Lab

### Menjalankan Lab Ini:

```bash
cd /home/labuser/tools/lab-xss
docker compose up document-write-lab -d
```

Lihat port yang digunakan:
```bash
docker port xss-lab-document-write
```

Akses aplikasi di browser: `http://localhost:<port>`

## 🛑 Cara Menghentikan Lab

```bash
docker compose down document-write-lab
```

## 🎮 Cara Bermain

1. Buka aplikasi di browser
2. Coba masukkan konten sederhana
3. Perhatikan URL dan parameter yang digunakan
4. Analisis source code untuk menemukan titik injeksi
5. Pahami bagaimana document.write() memproses input
6. Buat payload XSS yang dieksekusi melalui document.write()
7. Eksekusi payload untuk mendapatkan flag

## 💡 Hint (Tanpa Spoiler)

### Hint 1 - Parameter Discovery
Perhatikan URL saat Anda merender konten. Parameter apa yang digunakan?

### Hint 2 - Source Code Analysis
Coba test dengan input: `hello` dan lakukan view-source. Cari fungsi `document.write()` di source code.

### Hint 3 - Understanding document.write()
Fungsi `document.write()` menulis HTML ke halaman. HTML apa yang bisa dieksekusi?

### Hint 4 - Script Injection
Jika `document.write()` menulis `<script>alert(1)</script>`, apa yang terjadi?

### Hint 5 - Tag Closing
Perhatikan struktur document.write() di source code. Apakah input Anda berada di dalam attribute atau string?

### Hint 6 - Clue Tersembunyi
Refresh halaman beberapa kali untuk melihat petunjuk berbeda di source code.

## 📚 Konsep Teknis

### Apa itu document.write()?

`document.write()` adalah method JavaScript yang menulis string HTML langsung ke document stream.

```javascript
document.write('<p>Hello</p>');
```

### Mengapa document.write() Berbahaya?

Ketika input pengguna ditambahkan ke `document.write()` tanpa sanitasi:

```javascript
// VULNERABLE
var userInput = '<?php echo $_GET['content']; ?>';
document.write(userInput);
```

Input pengguna dapat menyisipkan HTML dan JavaScript tag yang akan dieksekusi.

### Vector Serangan pada Lab Ini

```
GET /?content=<payload_xss>
```

Parameter `content` direfleksikan melalui fungsi `document.write()`.

### Mengapa Ini Vulnerable?

```php
<!-- VULNERABLE CODE -->
<script>
document.write('<div><?php echo $content; ?></div>');
</script>
```

Walaupun berada di dalam string JavaScript, `document.write()` akan mengecek output sebagai HTML, sehingga tag `<script>` dapat dieksekusi.

## 🔒 Solusi (Spoiler Warning!)

<details>
<summary>Klik untuk melihat solusi</summary>

### Payload Dasar untuk document.write() XSS

```
?content=<script>alert(1)</script>
```

### Penjelasan Payload:

1. `<script>` - Membuka tag script
2. `alert(1)` - Kode JavaScript yang dieksekusi
3. `</script>` - Menutup tag script

### Bagaimana Ini Bekerja?

Ketika `document.write()` dipanggil:
```javascript
document.write('<div><script>alert(1)</script></div>');
```

Browser akan:
1. Parse HTML yang ditulis oleh document.write()
2. Mengeksekusi tag `<script>` yang ada di dalamnya
3. Menjalankan `alert(1)`

### Payload untuk Mendapatkan Flag

```
?content=<script>alert(flagData)</script>
```

Atau menggunakan fungsi validasi:

```
?content=<script>window.validateFlag(flagData)</script>
```

### Payload Alternatif (Image Onerror)

```
?content=<img src=x onerror=alert(1)>
```

### Penjelasan Teknis

1. Input `content` direfleksikan melalui `document.write()`
2. `document.write()` menulis string ke document sebagai HTML
3. Browser mem-parsing output tersebut sebagai HTML
4. Tag `<script>` atau event handler seperti `onerror` dieksekusi
5. JavaScript mengakses variabel `flagData` yang berisi flag

### Keunikan document.write()

Berbeda dengan innerHTML atau manipulasi DOM lainnya:
- `document.write()` menulis langsung ke document stream
- Dapat mengeksekusi script tag secara otomatis
- Tidak memerlukan user interaction untuk trigger

### Perbaikan yang Disarankan

```php
<!-- SECURE CODE -->
<script>
var safeContent = <?php echo json_encode($content); ?>;
document.getElementById('output').textContent = safeContent;
</script>
```

Atau hindari `document.write()` sama sekali dan gunakan method yang lebih aman:
```javascript
document.getElementById('output').textContent = userContent;
```

</details>

## 📊 Checklist Pengerjaan

- [ ] Menemukan parameter vulnerable
- [ ] Mengidentifikasi penggunaan document.write()
- [ ] Memahami bahwa document.write() mengeksekusi HTML
- [ ] Membuat payload XSS menggunakan script tag
- [ ] Mengeksekusi payload untuk mendapatkan flag
- [ ] Memahami risiko document.write()

## ⚠️ Catatan Penting

- Lab ini untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol
- Teknik yang dipelajari **jangan digunakan untuk tujuan ilegal**
- Fokus pada pemahaman konsep keamanan, bukan sekadar mendapatkan flag
- **document.write() sudah deprecated** dan sebaiknya dihindari di aplikasi modern
- Selalu gunakan **sanitasi input** untuk setiap DOM manipulation

## 🔖 Level & Teknik

- **Level**: Easy
- **Teknik**: Reflected XSS via query parameter (document.write usage)
- **Konteks**: document.write() function
- **Parameter**: `content`
- **Filter**: None (no CSP, no WAF, no input sanitization)

## 🔗 Perbedaan dengan Lab Lain

| Lab | Konteks Injeksi | Parameter | Payload Contoh |
|-----|----------------|-----------|----------------|
| Search Query Lab | HTML body | `q` | `<script>alert(1)</script>` |
| Attribute Lab | HTML attribute | `search` | `" onload="alert(1)` |
| JS String Lab | JavaScript string | `message` | `'; alert(1); //` |
| **Document.write Lab** | **document.write()** | **content** | **`<script>alert(1)</script>`** |

## 📖 Referensi Tambahan

- [MDN - document.write()](https://developer.mozilla.org/en-US/docs/Web/API/Document/write)
- [OWASP - DOM Based XSS](https://owasp.org/www-community/attacks/DOM_Based_XSS)
- [PortSwigger - DOM XSS vulnerabilities](https://portswigger.net/web-security/cross-site-scripting/dom-based)

---

**Author**: IDS – CyberSec Academy Lab Authoring Guideline
**Version**: 1.0
**Last Updated**: 2025
