# Solusi Mendapatkan Flag - JavaScript String XSS Lab

## Deskripsi Lab
Lab ini berisi kerentanan cross-site scripting (XSS) yang dipantulkan dalam fungsionalitas pelacakan kueri pencarian di mana tanda kurung sudut di-encode. Refleksi terjadi di dalam string JavaScript. Aplikasi dengan benar meng-encode tanda kurung sudut tetapi gagal meng-escape karakter lain yang penting dalam konteks JavaScript.

## Lokasi Kerentanan
Kerentanan terletak di file `search.php` dalam kode JavaScript:
```javascript
var searchQuery = "<?php echo $search; ?>";
```

## Cara Mendapatkan Flag

### Langkah 1: Akses Lab
1. Navigasi ke direktori lab: `cd /root/tools/lab-xss/js_string_xss_lab`
2. Mulai server PHP: `php -S 0.0.0.0:8005`
3. Akses lab di browser: `http://[SERVER_IP]:8005/`

### Langkah 2: Identifikasi Kerentanan
1. Masukkan string alfanumerik acak di kotak pencarian
2. Perhatikan bahwa string dipantulkan di dalam string JavaScript di respons
3. Perhatikan bahwa tanda kurung sudut di-HTML encode (`<` menjadi `&lt;`, `>` menjadi `&gt;`)

### Langkah 3: Eksploitasi Kerentanan
Karena tanda kurung sudut di-HTML encode, Anda tidak dapat menggunakan tag `<script>`. Namun, Anda dapat keluar dari konteks string JavaScript menggunakan tanda kutip dan menyisipkan kode JavaScript.

Payload solusi: `'-alert(1)-'`

Saat payload ini dimasukkan ke dalam string JavaScript, menjadi:
```javascript
var searchQuery = "'-alert(1)-'";
```

Ini keluar dari konteks string dan mengeksekusi fungsi `alert(1)`.

### Langkah 4: Dapatkan Flag
Setelah berhasil mengeksekusi serangan XSS, Anda akan melihat alert dengan pesan:
```
Congratulations! Flag: IDS{92798f74bc5cb240a73f2c9a8660c5ef}
```

## Payload Alternatif
Payload lain yang mungkin bekerja:
- `';alert(1);'`
- `';alert(1)//`
- `';alert(1)`

## Penjelasan Teknis
Aplikasi hanya meng-encode tanda kurung sudut tetapi tidak menangani dengan benar karakter lain seperti tanda kutip dan garis miring terbalik, yang memungkinkan penyerang untuk keluar dari konteks string JavaScript.

## Flag
Flag yang akan muncul saat Anda berhasil mengeksekusi serangan XSS:
```
IDS{92798f74bc5cb240a73f2c9a8660c5ef}
```