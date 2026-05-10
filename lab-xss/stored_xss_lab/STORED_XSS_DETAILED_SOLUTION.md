# Detailed Solution: Stored XSS Lab

## Lab Objective
Submit a comment that calls the `alert` function when the blog post is viewed.

## Step-by-Step Solution

### 1. Understanding the Vulnerability
The blog post page has a comment functionality that allows users to submit comments. The vulnerability exists in two places:
- Input validation: User input is not sanitized before storage
- Output encoding: Stored comments are displayed directly without HTML encoding

The PHP code in `blog_post.php` does the following:
1. Stores user comments directly without sanitization
2. Displays comments directly without encoding

### 2. Crafting the Payload
The simplest payload to trigger an alert is:
```
<script>alert('XSS')</script>
```

### 3. Executing the Attack
1. Navigate to `blog_post.php`
2. Enter the payload `<script>alert('XSS')</script>` in the comment box
3. Submit the comment
4. The JavaScript code will be stored in the comments file
5. When the page is loaded again, the stored JavaScript will execute

### 4. Alternative Payloads
If the basic script tag is filtered, you can try:

- Image tag with onerror event:
  ```
  <img src=x onerror=alert('XSS')>
  ```

- SVG element with onload event:
  ```
  <svg onload=alert('XSS')>
  ```

- Using event handlers:
  ```
  <div onclick=alert('XSS') style="width:100px;height:100px;">Click me</div>
  ```

## Technical Analysis

### Why This Works
1. User input is stored directly without sanitization
2. When the page loads, stored comments are embedded directly in HTML
3. The browser interprets the `<script>` tag as executable JavaScript
4. The `alert()` function is called, demonstrating script execution

### Stored vs Reflected XSS
- **Stored XSS**: Malicious script is permanently stored on the server and affects all users who view the content
- **Reflected XSS**: Malicious script is part of the request and only affects the user who crafts the request

### HTML Context Considerations
In this lab, the stored comments appear directly in the HTML context between tags, making it possible to inject:
- Script tags
- Event handlers
- Other HTML elements with embedded JavaScript

## Persistence Mechanism
The application stores comments in a text file (`comments.txt`) using PHP's `serialize()` function. When the page is loaded, it reads the comments using `unserialize()` and directly outputs them to the HTML without any sanitization.

## Flag
The flag for this lab is: `IDS{1c8a5c15517d898e873a11dd32a19fa4}`

## Prevention Measures

To prevent this vulnerability, the application should:

1. **Input Sanitization**: Sanitize user input before storing it
2. **Output Encoding**: HTML-encode special characters (`<`, `>`, `&`, `"`, `'`) when displaying user content
3. **Content Security Policy (CSP)**: Implement a strong CSP header to restrict script execution
4. **Use Framework Escaping**: Use templating systems with automatic escaping

Example of proper encoding in PHP:
```php
// When storing: htmlspecialchars($input, ENT_QUOTES, 'UTF-8')
// When displaying: htmlspecialchars($stored_data, ENT_QUOTES, 'UTF-8')
```

This would convert special characters to their HTML entities, preventing script execution.