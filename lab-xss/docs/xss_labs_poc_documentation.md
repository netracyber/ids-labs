# XSS Lab Collection - Documentation and POCs

## Lab 1: Reflected XSS Lab - Search Feature

### Description
This lab simulates a reflected XSS vulnerability in a search feature. User input from the search parameter is directly reflected in the page without proper sanitization.

### Vulnerability Type
Reflected XSS (Server-side reflected to client)

### Target
Search input field that reflects user input directly to the page

### POC for Reflected XSS Lab:
```
URL: reflected_xss_lab.html?q=<script>alert('XSS')</script>
OR
URL: reflected_xss_lab.html?q="><img src=x onerror=alert('XSS')>
OR
URL: reflected_xss_lab.html?q=javascript:alert('XSS')
```

### Exploitation Steps:
1. Navigate to the lab page
2. Either:
   - Modify the URL to include the payload in the `q` parameter
   - Or enter the payload in the search box and submit
3. The payload will execute and show the flag

### Flag
`IDS{reflected_xss_success_12345}`

---

## Lab 2: DOM-based XSS Lab - Document.Write

### Description
This lab simulates a DOM-based XSS vulnerability where user input from URL parameters or form inputs is used in client-side JavaScript without proper sanitization.

### Vulnerability Type
DOM-based XSS (Client-side only)

### Target
Name input field that gets reflected via client-side JavaScript

### POC for DOM XSS Lab:
```
URL: dom_xss_lab.html?name=<img src=x onerror=alert('DOM-XSS')>
OR
Input field: <svg onload=alert('XSS')>
OR
Input field: "><script>alert('XSS')</script>
```

### Exploitation Steps:
1. Navigate to the lab page
2. Either:
   - Modify the URL to include the payload in the `name` parameter
   - Or enter the payload in the input field and click "Update Greeting"
3. The payload will execute in the DOM context and show the flag

### Flag
`IDS{dom_xss_success_67890}`

---

## Lab 3: Stored XSS Lab - Comment System

### Description
This lab simulates a stored XSS vulnerability in a comment system. User input from comment fields is stored and then displayed to all visitors without proper sanitization.

### Vulnerability Type
Stored XSS (Persistent, stored on server/client storage)

### Target
Comment form fields that store and reflect user input to all viewers

### POC for Stored XSS Lab:
```
Author field: <script>alert('Stored-XSS')</script>
Comment field: Any comment text

OR

Author field: "><img src=x onerror=alert('Stored')>
Comment field: Test comment

OR

Author field: Anonymous
Comment field: <iframe src="javascript:alert(`XSS`)">
```

### Exploitation Steps:
1. Navigate to the lab page
2. Fill in the comment form with XSS payload in either Author or Comment field
3. Click "Post Comment"
4. The comment will be stored and displayed
5. When the page reloads or others view the page, the payload executes and shows the flag

### Flag
`IDS{stored_xss_success_abcde}`

---

## Summary of XSS Types Demonstrated:

1. **Reflected XSS**: 
   - Occurs when user input is immediately reflected back in the response
   - One-time execution when URL is accessed
   - Requires social engineering to exploit others

2. **DOM-based XSS**: 
   - Occurs when client-side JavaScript processes user input insecurely
   - Payload is processed entirely on the client side
   - Often harder to detect as it doesn't involve server processing

3. **Stored XSS**: 
   - Occurs when malicious input is stored on the server/database
   - Affects all users who view the infected page
   - Most dangerous as it has persistent impact

Each lab demonstrates a different aspect of XSS vulnerabilities and requires different exploitation techniques.