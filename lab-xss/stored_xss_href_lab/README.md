# Stored XSS into anchor href attribute with double quotes HTML-encoded - Lab

## Lab Overview
This lab contains a stored cross-site scripting vulnerability in the comment functionality. The vulnerability occurs when user input from the "Website" field is stored and later reflected in an anchor href attribute without proper sanitization. Although double quotes are HTML-encoded, the application is still vulnerable to JavaScript URL injection.

## Vulnerability Details
The vulnerability exists in the comment submission form where the "Website" input field is stored and later reflected inside an anchor href attribute. While double quotes are HTML-encoded (`"` becomes `&quot;`), the application fails to properly validate or sanitize JavaScript URLs, allowing attackers to inject malicious scripts.

The vulnerable code pattern looks like:
```php
<a href="<?php echo $website; ?>"><?php echo $author; ?></a>
```

## Lab Objective
To solve this lab, submit a comment that calls the alert function when the comment author name is clicked.

## Technical Details
- **Type**: Stored XSS
- **Context**: Anchor href attribute
- **Encoding**: Double quotes are HTML-encoded
- **Location**: Comment functionality in submit_comment.php and view_post.php
- **Flag**: `IDS{45f13c540e8997d935911c9987e167f6}`

## Setup Instructions
1. Navigate to the lab directory: `cd /root/tools/lab-xss/stored_xss_href_lab`
2. Start the PHP server: `php -S 0.0.0.0:8006`
3. Access the lab in your browser at `http://[SERVER_IP]:8006/`

## Solution Approach
1. Post a comment with a random alphanumeric string in the "Website" input
2. Observe that the string is reflected inside an anchor href attribute
3. Replace your input with the following payload to inject a JavaScript URL that calls alert:
   `javascript:alert(1)`
4. When clicking the author name (which is a link to the website), the alert should trigger
5. The flag will appear in an alert dialog when the XSS is successfully executed