# Web Security Academy XSS Labs - Complete Summary

## Lab Overview
This repository contains three cross-site scripting (XSS) labs that demonstrate different types of XSS vulnerabilities:

1. **Reflected XSS Lab**: A reflected XSS vulnerability in search functionality
2. **Stored XSS Lab**: A stored XSS vulnerability in comment functionality  
3. **DOM XSS Lab**: A DOM-based XSS vulnerability using document.write with location.search

## Lab Details

### 1. Reflected XSS Lab
- **Port**: 8001
- **Type**: Reflected XSS
- **Context**: HTML context (no encoding)
- **Location**: Search functionality in `search.php`
- **Issue**: Direct echo of user input without sanitization
- **Flag**: `IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}`
- **Access**: `http://[SERVER_IP]:8001/`

### 2. Stored XSS Lab
- **Port**: 8002
- **Type**: Stored XSS
- **Context**: HTML context (no encoding)
- **Location**: Comment functionality in `blog_post.php`
- **Issue**: Direct storage and display of user input without sanitization
- **Flag**: `IDS{1c8a5c15517d898e873a11dd32a19fa4}`
- **Access**: `http://[SERVER_IP]:8002/`

### 3. DOM XSS Lab
- **Port**: 8003
- **Type**: DOM-based XSS
- **Context**: JavaScript context (document.write with location.search)
- **Location**: JavaScript code in `dom_xss.html`
- **Issue**: Using document.write with data from location.search
- **Flag**: `IDS{6326ea06ab28fe9c08cd27189395a62e}`
- **Access**: `http://[SERVER_IP]:8003/dom_xss.html`

## Exploitation

### Reflected XSS
The vulnerability can be exploited by entering malicious JavaScript in the search box:
```
<script>alert('XSS')</script>
```

### Stored XSS
The vulnerability can be exploited by submitting malicious JavaScript in the comment form:
```
<script>alert('XSS')</script>
```

### DOM XSS
The vulnerability can be exploited by crafting a URL with a malicious payload:
```
http://[SERVER_IP]:8003/dom_xss.html?search=<script>alert('XSS')</script>
```

## Learning Objectives
1. Understand how reflected XSS works
2. Understand how stored XSS works
3. Understand how DOM-based XSS works
4. Recognize vulnerable code patterns
5. Learn how to identify different XSS contexts
6. Understand prevention techniques

## Setup
1. Run `./start_reflected_xss.sh` to start the reflected XSS server (port 8001)
2. Run `./start_stored_xss.sh` to start the stored XSS server (port 8002)
3. Run `./start_dom_xss.sh` to start the DOM XSS server (port 8003)
4. Visit the respective URLs in your browser
5. Follow the instructions to exploit each vulnerability

## Security Concepts Covered
- Input validation and sanitization
- Output encoding
- HTML context vulnerabilities
- JavaScript context vulnerabilities
- DOM manipulation vulnerabilities
- Client-side scripting risks
- Difference between reflected, stored, and DOM-based XSS