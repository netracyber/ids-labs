# Web Security Academy XSS Labs - Summary

## Lab Overview
This repository contains two cross-site scripting (XSS) labs that demonstrate different types of XSS vulnerabilities in HTML contexts with no encoding:

1. **Reflected XSS Lab**: A reflected XSS vulnerability in search functionality
2. **Stored XSS Lab**: A stored XSS vulnerability in comment functionality

## Files Created
- `index.html` - Main landing page with access to both labs
- `search.php` - Vulnerable search page for reflected XSS lab
- `blog_post.php` - Vulnerable blog post with comment functionality for stored XSS lab
- `comments.txt` - File to store comments (created automatically)
- `README.md` - Main documentation
- `SOLUTION.md` - Basic solution guide for reflected XSS
- `DETAILED_SOLUTION.md` - In-depth technical analysis for reflected XSS
- `STORED_XSS_README.md` - Documentation for stored XSS lab
- `STORED_XSS_SOLUTION.md` - Solution guide for stored XSS
- `STORED_XSS_DETAILED_SOLUTION.md` - Detailed solution for stored XSS
- `SUMMARY.md` - This file
- `start_server.sh` - Script to start the PHP server

## Vulnerability Details

### Reflected XSS Lab
- **Type**: Reflected XSS
- **Context**: HTML context (no encoding)
- **Location**: Search functionality in `search.php`
- **Issue**: Direct echo of user input without sanitization
- **Flag**: `IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}`

### Stored XSS Lab
- **Type**: Stored XSS
- **Context**: HTML context (no encoding)
- **Location**: Comment functionality in `blog_post.php`
- **Issue**: Direct storage and display of user input without sanitization
- **Flag**: `IDS{1c8a5c15517d898e873a11dd32a19fa4}`

## Exploitation

### Reflected XSS
The vulnerability can be exploited by entering malicious JavaScript in the search box:
```
<script>alert('XSS')</script>
```

When submitted, this JavaScript will execute in the user's browser, demonstrating the XSS vulnerability.

### Stored XSS
The vulnerability can be exploited by submitting malicious JavaScript in the comment form:
```
<script>alert('XSS')</script>
```

When submitted, this JavaScript will be stored and executed when the page is viewed again, demonstrating the stored XSS vulnerability.

## Learning Objectives
1. Understand how reflected XSS works
2. Understand how stored XSS works
3. Recognize vulnerable code patterns
4. Learn how to identify HTML context vulnerabilities
5. Understand prevention techniques

## Setup
1. Run `./start_server.sh` to start the server
2. Visit `http://[SERVER_IP]:8001` in your browser
3. Follow the instructions to exploit both vulnerabilities

## Security Concepts Covered
- Input validation and sanitization
- Output encoding
- HTML context vulnerabilities
- Client-side scripting risks
- Difference between reflected and stored XSS