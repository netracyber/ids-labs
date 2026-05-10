# innerHTML XSS Lab - Reflected XSS via innerHTML injection (Easy)

## 📋 Deskripsi Lab

Selamat datang di **innerHTML XSS Lab**! Lab ini mensimulasikan sebuah aplikasi note-taking yang memiliki kerentanan **Reflected Cross-Site Scripting (XSS)** melalui penggunaan properti **`innerHTML`**.

Aplikasi "QuickNote Pro" merender input pengguna menggunakan `element.innerHTML` tanpa sanitasi yang tepat.

## 🎯 Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

- Konsep **Reflected XSS melalui innerHTML**
- Bagaimana `innerHTML` dapat mengeksekusi JavaScript
- Perbedaan innerHTML dengan textContent
- Risiko keamanan dari penggunaan innerHTML dengan input pengguna
- Praktik terbaik untuk manipulasi DOM yang aman

## 🚀 Cara Menjalankan Lab

### Menjalankan Lab Ini:

```bash
cd /home/labuser/tools/lab-xss
docker compose up innerhtml-lab -d
```

Lihat port yang digunakan:
```bash
docker port xss-lab-innerhtml
```

Akses aplikasi di browser: `http://localhost:<port>`

## 🛑 Cara Menghentikan Lab

```bash
docker compose down innerhtml-lab
```

## 🎮 Cara Bermain

1. Buka aplikasi di browser
2. Coba masukkan catatan sederhana
3. Perhatikan URL dan parameter yang digunakan
4. Analisis source code untuk menemukan titik injeksi
5. Pahami bagaimana innerHTML memproses input
6. Buat payload XSS yang dieksekusi melalui innerHTML
7. Eksekusi payload untuk mendapatkan flag

## 💡 Hint (Tanpa Spoiler)

### Hint 1 - Parameter Discovery
Perhatikan URL saat Anda menyimpan catatan. Parameter apa yang digunakan?

### Hint 2 - Source Code Analysis
Coba test dengan input: `hello` dan lakukan view-source. Cari penggunaan `innerHTML` di JavaScript.

### Hint 3 - Understanding innerHTML
Fungsi `innerHTML` menulis HTML ke dalam elemen. Tag HTML apa yang bisa dieksekusi?

### Hint 4 - Script Execution
Apakah `<script>` tag langsung dieksekusi saat dimasukkan melalui innerHTML?

### Hint 5 - Event Handlers
Jika script tag tidak bekerja langsung, coba gunakan event handler seperti `onload` atau `onerror`.

### Hint 6 - Clue Tersembunyi
Refresh halaman beberapa kali untuk melihat petunjuk berbeda di source code.

## 📚 Konsep Teknis

### Apa itu innerHTML?

`innerHTML` adalah properti DOM yang mengatur atau mengembalikan HTML di dalam elemen.

```javascript
element.innerHTML = '<p>Hello</p>';
```

### Mengapa innerHTML Berbahaya?

Ketika input pengguna ditetapkan ke `innerHTML` tanpa sanitasi:

```javascript
// VULNERABLE
var userInput = '<?php echo $_GET['note']; ?>';
document.getElementById('output').innerHTML = userInput;
```

Input pengguna dapat menyisipkan HTML dan JavaScript yang akan dieksekusi.

### Perbedaan innerHTML vs textContent

| Method | Behavior | Safe for User Input? |
|--------|----------|---------------------|
| `innerHTML` | Parse dan render HTML | ❌ Tidak aman |
| `textContent` | Set sebagai teks plain | ✅ Aman |

### Vector Serangan pada Lab Ini

```
GET /?note=<payload_xss>
```

Parameter `note` direfleksikan melalui properti `innerHTML`.

### Mengapa Ini Vulnerable?

```javascript
// VULNERABLE CODE (line ~200)
const noteContent = <?php echo json_encode($note); ?>;
document.getElementById('note-output').innerHTML = noteContent;
```

Walaupun menggunakan `json_encode()`, JavaScript tetap akan mem-parse output tersebut sebagai HTML ketika dimasukkan ke `innerHTML`.

## 🔒 Solusi (Spoiler Warning!)

<details>
<summary>Klik untuk melihat solusi</summary>

### Payload Dasar untuk innerHTML XSS

**Method 1: Using img tag with onerror**
```
?note=<img src=x onerror=alert(1)>
```

**Penjelasan:**
1. `<img>` - Membuat tag gambar
2. `src=x` - Source yang tidak valid (akan menyebabkan error)
3. `onerror=alert(1)` - Event handler yang dieksekusi saat gambar error

**Method 2: Using svg tag**
```
?note=<svg onload=alert(1)>
```

**Penjelasan:**
1. `<svg>` - Membuka tag SVG
2. `onload=alert(1)` - Event handler yang dieksekusi saat SVG dimuat

### Mengapa Script Tag Tidak Langsung Bekerja?

```javascript
element.innerHTML = '<script>alert(1)</script>';
// Tidak akan dieksekusi!
```

Tag `<script>` yang dimasukkan melalui `innerHTML` **tidak** otomatis dieksekusi oleh browser sebagai security measure. Oleh karena itu, kita perlu menggunakan event handler seperti `onerror` atau `onload`.

### Payload untuk Mendapatkan Flag

```
?note=<img src=x onerror=alert(flagData)>
```

Atau menggunakan fungsi validasi:

```
?note=<img src=x onerror=window.validateFlag(flagData)>
```

### Payload Alternatif

```
?note=<svg onload=alert(flagData)>
```

Atau menggunakan iframe:

```
?note=<iframe src="javascript:alert(flagData)">
```

### Penjelasan Teknis

1. Input `note` direfleksikan melalui `innerHTML`
2. `innerHTML` mem-parsing string sebagai HTML
3. Tag seperti `<img>` atau `<svg>` dirender
4. Event handler (`onerror`, `onload`) dieksekusi
5. JavaScript mengakses variabel `flagData` yang berisi flag

### Perbaikan yang Disarankan

```javascript
// SECURE CODE - Option 1: Use textContent
document.getElementById('note-output').textContent = noteContent;

// SECURE CODE - Option 2: Sanitize HTML
function sanitizeHTML(html) {
    const temp = document.createElement('div');
    temp.textContent = html;
    return temp.innerHTML;
}
document.getElementById('note-output').innerHTML = sanitizeHTML(noteContent);
```

**Selalu gunakan `textContent` jika Anda hanya ingin menampilkan teks!**

</details>

## 📊 Checklist Pengerjaan

- [ ] Menemukan parameter vulnerable
- [ ] Mengidentifikasi penggunaan innerHTML
- [ ] Memahami bahwa script tag tidak langsung dieksekusi
- [ ] Membuat payload XSS menggunakan event handler
- [ ] Mengeksekusi payload untuk mendapatkan flag
- [ ] Memahami perbedaan innerHTML vs textContent

## ⚠️ Catatan Penting

- Lab ini untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol
- Teknik yang dipelajari **jangan digunakan untuk tujuan ilegal**
- Fokus pada pemahaman konsep keamanan, bukan sekadar mendapatkan flag
- **Hindari innerHTML untuk input pengguna** - gunakan textContent
- Selalu gunakan **sanitasi input** untuk setiap manipulasi DOM

## 🔖 Level & Teknik

- **Level**: Easy
- **Teknik**: Reflected XSS via query parameter (innerHTML injection)
- **Konteks**: innerHTML property
- **Parameter**: `note`
- **Filter**: None (no CSP, no WAF, no input sanitization)

## 🔗 Perbedaan dengan Lab Lain

| Lab | Konteks Injeksi | Parameter | Payload Contoh |
|-----|----------------|-----------|----------------|
| Search Query | HTML body (PHP echo) | `q` | `<script>alert(1)</script>` |
| Attribute | HTML attribute | `search` | `" onload="alert(1)` |
| JS String | JavaScript string | `message` | `'; alert(1); //` |
| Document.write | document.write() | `content` | `<script>alert(1)</script>` |
| **innerHTML** | **element.innerHTML** | **note** | **`<img src=x onerror=alert(1)>`** |

## 📖 Referensi Tambahan

- [MDN - innerHTML](https://developer.mozilla.org/en-US/docs/Web/API/Element/innerHTML)
- [MDN - textContent](https://developer.mozilla.org/en-US/docs/Web/API/Node/textContent)
- [OWASP - DOM Based XSS](https://owasp.org/www-community/attacks/DOM_Based_XSS)
- [PortSwigger - DOM XSS](https://portswigger.net/web-security/cross-site-scripting/dom-based)

---

**Author**: IDS – CyberSec Academy Lab Authoring Guideline
**Version**: 1.0
**Last Updated**: 2025
