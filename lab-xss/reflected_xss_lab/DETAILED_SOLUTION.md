# Detailed Solution: Reflected XSS Lab

## Lab Objective
Perform a cross-site scripting attack that calls the `alert` function.

## Step-by-Step Solution

### 1. Understanding the Vulnerability
The search functionality in `search.php` directly reflects the user's search term without any HTML encoding or sanitization. Specifically, this line in the PHP code is vulnerable:

```php
<?php echo $searchTerm; ?>
```

This means any HTML or JavaScript code entered in the search box will be directly rendered by the browser.

### 2. Crafting the Payload
The simplest payload to trigger an alert is:
```
<script>alert('XSS')</script>
```

### 3. Executing the Attack
1. Navigate to `http://localhost:8001`
2. Enter the payload `<script>alert('XSS')</script>` in the search box
3. Click the search button
4. The JavaScript code will execute and display an alert popup
5. A success flag will appear on the page confirming the XSS execution

### 4. Alternative Payloads
If the basic script tag is filtered, you can try:

- Image tag with onerror event:
  ```
  <img src=x onerror=alert('XSS')>
  ```
  (Note: This needs to be URL encoded when submitted via URL: `%3Cimg%20src=x%20onerror=alert%28%27XSS%27%29%3E`)

- SVG element with onload event:
  ```
  <svg onload=alert('XSS')>
  ```

- Using event handlers:
  ```
  <div onclick=alert('XSS') style="width:100px;height:100px;">Click me</div>
  ```

## Success Confirmation

When you successfully execute an XSS attack, the following flag will appear on the page:
```
Flag: IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}
```

The application detects XSS attempts by checking for common XSS patterns in the input, including:
- Script tags
- Event handlers (onerror, onload, onclick, etc.)
- Image tags with JavaScript
- SVG elements with JavaScript
- Other common XSS vectors

## Technical Analysis

### Why This Works
1. User input is directly embedded in HTML without sanitization
2. The browser interprets the `<script>` tag as executable JavaScript
3. The `alert()` function is called, demonstrating script execution

### HTML Context Considerations
In this lab, the reflected input appears directly in the HTML context between tags, making it possible to inject:
- Script tags
- Event handlers
- Other HTML elements with embedded JavaScript

## Prevention Measures

To prevent this vulnerability, the application should:

1. **Encode Output**: HTML-encode special characters (`<`, `>`, `&`, `"`, `'`) before reflecting user input
2. **Input Validation**: Validate and sanitize user input
3. **Content Security Policy (CSP)**: Implement a strong CSP header to restrict script execution
4. **Use Framework Escaping**: Use templating systems with automatic escaping

Example of proper encoding in PHP:
```php
// Instead of: <?php echo $searchTerm; ?>
// Use: <?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>
```

This would convert special characters to their HTML entities, preventing script execution.