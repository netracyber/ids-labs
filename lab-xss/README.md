# XSS Labs Collection - CyberSec Academy

## Deskripsi

Kumpulan lab keamanan web untuk mempelajari berbagai teknik **Cross-Site Scripting (XSS)**. Setiap lab dirancang untuk pembelajaran hands-on dengan tingkat kesulitan yang bervariasi.

## 📚 Daftar Lab

| # | Nama Lab | Teknik | Level | Port |
|---|----------|--------|-------|------|
| 1 | Reflected XSS Lab | Reflected XSS | Easy | 8020 |
| 2 | Stored XSS Lab | Stored XSS | Medium | 8021 |
| 3 | DOM XSS Lab | DOM-based XSS | Medium | 8022 |
| 4 | DOM innerHTML XSS Lab | DOM innerHTML | Medium | 8023 |
| 5 | JS String XSS Lab | JavaScript Context | Medium | 8024 |
| 6 | Stored XSS Href Lab | Stored in href | Hard | 8025 |
| 7 | JS Context XSS Lab | JS Context | Hard | 8026 |
| 8 | JSON XSS Lab | JSON Injection | Hard | 8027 |
| 9 | Formaction XSS Lab | Formaction Bypass | Hard | 8028 |
| 10 | Hash innerHTML XSS Lab | Hash-based | Medium | 8029 |
| 11 | **Search Query XSS Lab** | **Reflected Body** | **Easy** | **Random** |
| 12 | **Attribute XSS Lab** | **Reflected Attribute** | **Easy** | **Random** |
| 13 | **JS String Context Lab** | **Reflected JS String** | **Easy** | **Random** |
| 14 | **Document.write Lab** | **Reflected document.write** | **Easy** | **Random** |
| 15 | **innerHTML Lab** | **Reflected innerHTML** | **Easy** | **Random** |
| 16 | **LFI Medium-Hard Lab** | **Local File Inclusion** | **Medium-Hard** | **8039** |

## 🆕 Lab Baru

### Search Query XSS Lab
Reflected XSS melalui query parameter pada aplikasi pencarian sederhana.
- **Parameter**: `q`
- **Context**: HTML body
- **Start**: `docker compose up search-query-xss-lab -d`
- **Docs**: [search_query_xss_lab/README.md](search_query_xss_lab/README.md)

### Attribute XSS Lab
Reflected XSS dalam **HTML attribute context** pada formulir pencarian.
- **Parameter**: `search`
- **Context**: HTML attribute (`value=""`)
- **Start**: `docker compose up attribute-xss-lab -d`
- **Docs**: [attribute_xss_lab/README.md](attribute_xss_lab/README.md)

### JS String Context XSS Lab
Reflected XSS dalam **JavaScript string context** pada aplikasi messaging.
- **Parameter**: `message`
- **Context**: JavaScript string variable
- **Start**: `docker compose up js-string-context-lab -d`
- **Docs**: [js_string_context_lab/README.md](js_string_context_lab/README.md)

### Document.write XSS Lab
Reflected XSS melalui fungsi **`document.write()`** pada aplikasi dynamic rendering.
- **Parameter**: `content`
- **Context**: document.write() function
- **Start**: `docker compose up document-write-lab -d`
- **Docs**: [document_write_lab/README.md](document_write_lab/README.md)

### innerHTML XSS Lab (NEW!)
Reflected XSS melalui properti **`innerHTML`** pada aplikasi note-taking.
- **Parameter**: `note`
- **Context**: element.innerHTML property
- **Start**: `docker compose up innerhtml-lab -d`
- **Docs**: [innerhtml_lab/README.md](innerhtml_lab/README.md)

### LFI Medium-Hard Lab
Lab standalone untuk **Local File Inclusion / Path Traversal** dengan filter traversal sederhana yang bisa dibypass.
- **Parameter**: `doc`
- **Context**: file path resolution
- **Start**: `cd lab-xss-lfi-medium-hard && docker-compose up -d`
- **Docs**: [lab-xss-lfi-medium-hard/README.md](lab-xss-lfi-medium-hard/README.md)

#### Perbedaan Kelima Konteks Injeksi:

| Lab | Parameter | Context | Payload Contoh |
|-----|-----------|---------|----------------|
| Search Query | `q` | HTML body | `<script>alert(1)</script>` |
| Attribute | `search` | HTML attribute | `" onload="alert(1)` |
| JS String | `message` | JavaScript string | `'; alert(1); //` |
| Document.write | `content` | document.write() | `<script>alert(1)</script>` |
| **innerHTML** | **note** | **element.innerHTML** | **`<img src=x onerror=alert(1)>`** |

## 🚀 Cara Menjalankan Semua Lab

```bash
cd /home/labuser/tools/lab-xss
docker-compose up -d
```

Lihat status semua lab:
```bash
docker-compose ps
```

## 🛑 Cara Menghentikan Lab

### Semua Lab:
```bash
docker-compose down
```

### Lab Tertentu:
```bash
docker-compose down search-query-xss-lab
```

## 📖 Panduan Umum

### Langkah Dasar Menyelesaikan Lab XSS:
1. **Identifikasi Parameter** - Temukan input yang dikirim ke server
2. **Analisis Reflection** - Lihat di mana input muncul kembali
3. **Tentukan Konteks** - Pahami context (HTML, attribute, JavaScript, dll)
4. **Buat Payload** - Susun payload XSS yang sesuai dengan konteks
5. **Eksekusi** - Jalankan payload untuk mendapatkan flag
6. **Validasi** - Flag muncul melalui JavaScript alert()

### Hint Umum:

💡 **Hint 1**: Perhatikan parameter apa saja yang digunakan oleh aplikasi.

💡 **Hint 2**: Coba test input sederhana dan lihat bagaimana aplikasi merespons.

💡 **Hint 3**: Jika input Anda muncul kembali di halaman, itu indikasi potensi Reflected XSS.

💡 **Hint 4**: View-source bisa membantu memahami struktur aplikasi.

💡 **Hint 5**: Clue tersembunyi tersimpan di tempat yang tidak terlihat langsung.

## 🎯 Tingkat Kesulitan

### Easy
- Tidak ada CSP
- Tidak ada WAF
- Tidak ada input filtering
- Single-step exploit

### Medium
- Basic filtering atau encoding
- Perlu context awareness
- Mungkin melibatkan DOM-based XSS

### Hard
- Multi-step atau chained execution
- Mengandalkan logic flaw
- Tidak ada reflection langsung

## ⚠️ Catatan Penting

- Lab ini untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol
- Teknik yang dipelajari **jangan digunakan untuk tujuan ilegal**
- Fokus pada pemahaman konsep, bukan sekadar mendapatkan flag
- Selalu gunakan **output encoding** dan **Content Security Policy (CSP)** di aplikasi nyata

## 📚 Referensi

- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [PortSwigger XSS Guide](https://portswigger.net/web-security/cross-site-scripting)
- [MDN Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)

---

**Author**: IDS – CyberSec Academy Lab Authoring Guideline
**Version**: 2.0
**Last Updated**: 2025
