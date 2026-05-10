# Additional XSS Lab Collection - Documentation and POCs

## Lab 4: JSON-based XSS Lab - API Response

### Description
This lab simulates an XSS vulnerability where user input is reflected in what appears to be an API response. The vulnerability occurs when JSON data containing user input is improperly handled in an HTML context.

### Vulnerability Type
JSON-based XSS / Context Confusion XSS

### Target
Username input field that gets reflected in both JSON response and HTML context

### POC for JSON-based XSS Lab:
```
Input field: <img src=x onerror=alert('JSON-XSS')>
OR
Input field: <script>alert('XSS')</script>
OR
Input field: javascript:alert('XSS')
```

### Exploitation Steps:
1. Enter XSS payload in the username field
2. Click "Get User Details"
3. The payload will be processed in the simulated API response
4. When the response is rendered in HTML context, the XSS executes
5. Flag appears when XSS is detected

### Flag
`IDS{json_xss_success_54321}`

---

## Lab 5: JavaScript Context XSS Lab - Eval Injection

### Description
This lab simulates an XSS vulnerability in a JavaScript context where user input is passed directly to eval() function. This creates a dangerous scenario where JavaScript code injection is possible.

### Vulnerability Type
JavaScript Context XSS / Eval Injection

### Target
Expression input field that gets passed to eval() function

### POC for JavaScript Context XSS Lab:
```
Input field: alert('JS-XSS')
OR
Input field: alert`XSS`
OR
Input field: document.body.innerHTML='<img src=x onerror=alert("XSS")>'
OR
Input field: (function(){alert('XSS')})()
```

### Exploitation Steps:
1. Enter JavaScript code as expression in the input field
2. Click "Calculate"
3. The eval() function will execute the JavaScript code
4. When the malicious code runs, the flag appears

### Flag
`IDS{js_context_xss_98765}`

---

## Lab 6: Template Injection XSS Lab - String Concatenation

### Description
This lab simulates an XSS vulnerability in a template system where user input is concatenated directly into templates without proper sanitization. This allows for template injection attacks.

### Vulnerability Type
Template Injection XSS / String Concatenation Vulnerability

### Target
Name input field that gets injected into template placeholders

### POC for Template Injection XSS Lab:
```
Input field: ${alert('XSS')}  (Template literal injection)
OR
Input field: <script>alert('XSS')</script>
OR
Input field: ${constructor.constructor('alert("XSS")')()}
OR
Input field: ${document.domain}
```

### Exploitation Steps:
1. Enter template injection payload in the name field
2. Click "Generate Message"
3. The template engine will process the user input
4. If the payload is valid template syntax, it will execute
5. Flag appears when template injection/XSS is detected

### Flag
`IDS{template_xss_13579}`

---

## Summary of Additional XSS Types Demonstrated:

4. **JSON-based XSS**: 
   - Occurs when data meant for one context (JSON) is used in another (HTML)
   - Exploits context confusion between data formats
   - Often seen in APIs that return JSON but are used in HTML contexts

5. **JavaScript Context XSS**: 
   - Occurs when user input is executed as JavaScript code
   - Often through eval(), Function(), or similar dynamic code execution
   - Very dangerous as it allows full code execution

6. **Template Injection XSS**: 
   - Occurs when template engines process user input as template code
   - Allows injection of template syntax to execute arbitrary code
   - Common in modern web frameworks with template systems

Each of these labs demonstrates advanced XSS techniques beyond the basic reflected, stored, and DOM-based XSS types.