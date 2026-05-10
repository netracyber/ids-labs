# Reflected XSS into a JavaScript String with Angle Brackets HTML Encoded - Lab

## Lab Overview
This lab contains a reflected cross-site scripting vulnerability in the search query tracking functionality where angle brackets are encoded. The reflection occurs inside a JavaScript string. The application properly encodes angle brackets but fails to properly escape other characters that are significant in JavaScript contexts.

## Vulnerability Details
The vulnerability exists in the search functionality where user input is reflected inside a JavaScript string without proper escaping. Although angle brackets are HTML encoded (`<` becomes `&lt;`, `>` becomes `&gt;`), other characters like quotes and backslashes are not properly handled, allowing attackers to break out of the JavaScript string context.

The vulnerable code looks like:
```php
var searchQuery = "<?php echo $search; ?>";
```

## Lab Objective
To solve this lab, perform a cross-site scripting attack that breaks out of the JavaScript string and calls the alert function.

## Technical Details
- **Type**: Reflected XSS
- **Context**: JavaScript string
- **Encoding**: Angle brackets are HTML encoded
- **Location**: JavaScript code in search.php
- **Flag**: `IDS{92798f74bc5cb240a73f2c9a8660c5ef}`

## Setup Instructions
1. Navigate to the lab directory: `cd /root/tools/lab-xss/js_string_xss_lab`
2. Start the PHP server: `php -S 0.0.0.0:8005`
3. Access the lab in your browser at `http://[SERVER_IP]:8005/`