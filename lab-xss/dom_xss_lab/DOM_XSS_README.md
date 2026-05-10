# DOM XSS Lab - document.write with location.search

This lab demonstrates a DOM-based cross-site scripting vulnerability that uses the JavaScript `document.write` function with data from `location.search`.

## Setup Instructions

1. Install PHP (if not already installed):
   ```bash
   sudo apt update
   sudo apt install php php-cli
   ```

2. Start the DOM XSS lab server:
   ```bash
   cd /root/tools/lab-xss/dom_xss_lab
   ./start_dom_xss.sh
   ```

3. Access the lab in your browser at `http://[SERVER_IP]:8003/dom_xss.html`

## Lab Overview

This lab simulates a search query tracking functionality that is vulnerable to DOM-based XSS. The vulnerability exists in the JavaScript code that uses `document.write` with data from `location.search`, which can be controlled via the URL.

## Vulnerability Details

- **Type**: DOM-based XSS
- **Location**: JavaScript code in `dom_xss.html`
- **Vulnerable Function**: `document.write()` 
- **Source**: `location.search` (URL query parameters)

## Exploitation

The vulnerability can be exploited by crafting a URL with a malicious payload in the search parameter:

```
http://[SERVER_IP]:8003/dom_xss.html?search=<script>alert('XSS')</script>
```

When a user visits this URL, the JavaScript code will execute and use `document.write` to output the search query, executing the malicious script.

## Solution

To solve the lab:

1. Navigate to the DOM XSS page
2. Add a search parameter with an XSS payload to the URL:
   ```
   http://[SERVER_IP]:8003/dom_xss.html?search=<script>alert('XSS')</script>
   ```
3. The JavaScript code will execute the payload via `document.write`

## Alternative Payloads

Other payloads that will work:
- `<img src=x onerror=alert('XSS')>`
- `<svg onload=alert('XSS')>`
- `javascript:alert('XSS')` (in some contexts)

## Flag
The flag for this lab is: `IDS{6326ea06ab28fe9c08cd27189395a62e}`

## Prevention

To prevent this vulnerability, the application should:

1. Avoid using `document.write` with untrusted data
2. Use safer DOM manipulation methods like `textContent` instead of `innerHTML`
3. Sanitize or encode user input before using it in DOM operations
4. Implement Content Security Policy (CSP) to restrict script execution