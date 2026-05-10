# Stored XSS Lab - HTML Context (No Encoding)

This lab demonstrates a stored cross-site scripting vulnerability where user input is stored and then reflected in the HTML context without any encoding.

## Setup Instructions

1. Install PHP (if not already installed):
   ```bash
   sudo apt update
   sudo apt install php php-cli
   ```

2. Start the Stored XSS lab server:
   ```bash
   cd /root/tools/lab-xss/stored_xss_lab
   ./start_stored_xss.sh
   ```

3. Access the lab in your browser at `http://[SERVER_IP]:8002`

## Vulnerability Details

The vulnerability exists in the comment functionality where user comments are stored without sanitization and then displayed directly in the HTML without proper encoding. The PHP code stores user comments and displays them without any sanitization.

## Solution

To exploit this vulnerability and solve the lab:

1. Go to the blog post page
2. Enter the following payload in the comment box:
   ```html
   <script>alert('XSS')</script>
   ```
3. Submit the comment
4. The JavaScript code will be stored and executed when the page is viewed again

Alternative payloads that will also work:
- `<img src=x onerror=alert('XSS')>`
- `<svg onload=alert('XSS')>`
- `<div onclick=alert('XSS') style="width:100px;height:100px;">Click me</div>`

## Flag
The flag for this lab is: `IDS{1c8a5c15517d898e873a11dd32a19fa4}`

## Files

- `stored_xss_index.html` - Main page for stored XSS lab
- `blog_post.php` - Vulnerable blog post with comment functionality
- `comments.txt` - File to store comments (created automatically)
- `start_stored_xss.sh` - Script to start the stored XSS lab server

## Security Concepts Demonstrated

- Stored XSS
- HTML context injection
- Lack of input sanitization
- Lack of output encoding
- Client-side scripting vulnerability