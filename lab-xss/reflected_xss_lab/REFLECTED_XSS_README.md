# Reflected XSS Lab - HTML Context (No Encoding)

This lab demonstrates a reflected cross-site scripting vulnerability where user input is directly reflected in the HTML context without any encoding.

## Setup Instructions

1. Install PHP (if not already installed):
   ```bash
   sudo apt update
   sudo apt install php php-cli
   ```

2. Start the Reflected XSS lab server:
   ```bash
   cd /root/tools/lab-xss/reflected_xss_lab
   ./start_reflected_xss.sh
   ```

3. Access the lab in your browser at `http://[SERVER_IP]:8001`

## Vulnerability Details

The vulnerability exists in the search functionality where the user's search term is directly reflected in the HTML without proper encoding. The PHP code in `search.php` takes the `search` parameter from the GET request and directly echoes it to the page without sanitization.

## Solution

To exploit this vulnerability and solve the lab:

1. Go to the search page
2. Enter the following payload in the search box:
   ```html
   <script>alert('XSS')</script>
   ```
3. Submit the search
4. The alert should execute, demonstrating the XSS vulnerability

Alternative payloads that will also work:
- `<img src=x onerror=alert('XSS')>`
- `javascript:alert('XSS')` (in some contexts)
- `<svg onload=alert('XSS')>`

## Flag
The flag for this lab is: `IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}`

## Files

- `reflected_xss_index.html` - Main page for reflected XSS lab
- `search.php` - Vulnerable search page that reflects user input without encoding
- `start_reflected_xss.sh` - Script to start the reflected XSS lab server

## Security Concepts Demonstrated

- Reflected XSS
- HTML context injection
- Lack of input sanitization
- Client-side scripting vulnerability