# Solution Guide: DOM XSS in innerHTML Sink using Source location.search

## Lab Objective
Find and exploit the XSS vulnerability in this blog application.

## Solution Approach

This is a DOM-based XSS challenge where you need to find how user input is handled in the search functionality.

1. Navigate to the lab in your browser at: `http://[SERVER_IP]:8004/`
2. Explore the search functionality and analyze how the search term is processed
3. Look for how the search term is reflected in the page without proper sanitization
4. Try different XSS payloads to exploit the vulnerability

## Finding the Vulnerability

The vulnerability is in the search functionality where user input from the URL parameter is directly inserted into the DOM using innerHTML without proper sanitization.

## Exploitation

Once you identify the vulnerable parameter, try payloads like:
- `<img src=1 onerror=alert(1)>`
- `<svg onload=alert(1)>`
- `<img src=x onerror=alert('XSS')>`

## Flag
When you successfully execute an XSS attack, the flag will be revealed:
```
Flag: IDS{e0b37cb9c327bc8a741bf11e6cd88025}
```

## Prevention

To prevent this vulnerability, the application should:
1. Avoid using innerHTML with user-controlled data
2. Use safer alternatives like textContent for inserting text
3. Sanitize user input before inserting into the DOM
4. Implement Content Security Policy (CSP) to limit script execution