# XSS Lab Collection - Updated POC Documentation

## Lab 4: JSON-based XSS Lab - API Response
**Port: 8007**
**Flag: IDS{b5eff72a5c19991966501c9e47a81025}**

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
5. An alert will appear showing: "Congratulations! Flag: IDS{b5eff72a5c19991966501c9e47a81025}"

### Flag
`IDS{b5eff72a5c19991966501c9e47a81025}`

---

## Lab 5: JavaScript Context XSS Lab - Eval Injection
**Port: 8008**
**Flag: IDS{b2ab1b73a5622d47dc47bd99783597b2}**

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
4. An alert will appear showing: "Congratulations! Flag: IDS{b2ab1b73a5622d47dc47bd99783597b2}"

### Flag
`IDS{b2ab1b73a5622d47dc47bd99783597b2}`

---

## Lab 6: Template Injection XSS Lab - String Concatenation
**Port: 8009**
**Flag: IDS{b3f9e2d2f0c63a99e31f8752e860b0e2}**

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
5. An alert will appear showing: "Congratulations! Flag: IDS{b3f9e2d2f0c63a99e31f8752e860b0e2}"

### Flag
`IDS{b3f9e2d2f0c63a99e31f8752e860b0e2}`

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

Each of these labs now displays the specific flag in an alert when XSS exploitation is successful.