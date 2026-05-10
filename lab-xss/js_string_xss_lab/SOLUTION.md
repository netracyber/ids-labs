# Solution Guide: Reflected XSS into a JavaScript String with Angle Brackets HTML Encoded

## Lab Objective
To solve this lab, perform a cross-site scripting attack that breaks out of the JavaScript string and calls the alert function.

## Solution Approach

This is a reflected XSS challenge where you need to exploit a vulnerability in a JavaScript string context.

1. Navigate to the lab in your browser at: `http://[SERVER_IP]:8005/`
2. Submit a random alphanumeric string in the search box
3. Observe that the string is reflected inside a JavaScript string in the response
4. Notice that angle brackets are HTML encoded (`<` becomes `&lt;`, `>` becomes `&gt;`)
5. Try the solution payload to break out of the JavaScript string

## Exploitation

The vulnerability occurs in the JavaScript code where the search query is reflected inside a string:

```javascript
var searchQuery = "<?php echo $search; ?>";
```

Since angle brackets are HTML encoded, you can't use `<script>` tags. However, you can break out of the JavaScript string using quotes and inject JavaScript code.

The solution payload is: `'-alert(1)-'`

When this payload is inserted into the JavaScript string, it becomes:
```javascript
var searchQuery = "'-alert(1)-'";
```

This breaks out of the string context and executes the `alert(1)` function.

## Alternative Solutions

Other payloads that might work:
- `';alert(1);'`
- `';alert(1)//`
- `';alert(1)`

## Flag
When you successfully execute an XSS attack, the following flag will appear:
```
Flag: IDS{92798f74bc5cb240a73f2c9a8660c5ef}
```

## Prevention

To prevent this vulnerability, the application should:
1. Properly escape all special characters in JavaScript contexts
2. Use JSON encoding when inserting data into JavaScript
3. Implement Content Security Policy (CSP) to limit script execution
4. Validate and sanitize user input