# XSS Labs - Difficulty Ratings

## Quick Reference

| Lab | Port | Difficulty | Time | Focus |
|-----|------|------------|------|-------|
| [Reflected XSS](#1-reflected-xss-lab) | 8001 | Easy | 5-10 min | Basic reflected XSS |
| [Stored XSS](#2-stored-xss-lab) | 8002 | Easy | 10-15 min | Persistent XSS |
| [DOM XSS](#3-dom-xss-lab) | 8003 | Medium | 15-20 min | Client-side DOM |
| [DOM innerHTML XSS](#4-dom-innerhtml-xss-lab) | 8004 | Medium | 15-25 min | innerHTML sink |
| [JS String XSS](#5-js-string-xss-lab) | 8005 | Hard | 20-30 min | Script context |
| [Stored XSS Href](#6-stored-xss-href-lab) | 8006 | Medium | 15-20 min | Attribute injection |
| [JS Context XSS](#7-js-context-xss-lab) | 8007 | Hard | 25-35 min | Complex JS context |
| [JSON XSS](#8-json-xss-lab) | 8008 | Hard | 25-35 min | JSON parsing |
| [Formaction XSS](#9-formaction-xss-lab) | 8009 | Easy | 10-15 min | POST-based formaction |
| [DOM Hash innerHTML XSS](#10-dom-hash-xss-lab) | 8010 | Easy | 10-15 min | location.hash + innerHTML |

---

## Overview

| Level | Description | Target Audience |
|--------|-------------|-----------------|
| **Beginner** | Basic XSS concepts, obvious vulnerabilities | New to XSS |
| **Easy** | Simple XSS, minimal filtering | Some XSS knowledge |
| **Medium** | Basic filtering, requires creativity | Comfortable with XSS basics |
| **Hard** | Advanced filtering, multiple layers | Experienced with XSS |
| **Expert** | Complex context, obfuscation needed | XSS expert |

---

## Lab Ratings

### 1. Reflected XSS Lab
**Port:** 8001 | **Difficulty:** **Easy**

**Description:** Classic reflected XSS where user input is reflected without proper encoding.

**Skills Required:**
- Understanding of reflected XSS
- Basic payload crafting
- HTML injection basics

**Example Payload:**
```html
<script>alert(document.domain)</script>
```

**Key Concepts:**
- Reflected vs Stored XSS
- Input validation
- Output encoding

**Estimated Time:** 5-10 minutes

---

### 2. Stored XSS Lab
**Port:** 8002 | **Difficulty:** **Easy**

**Description:** Stored XSS in blog comments where malicious payload is saved and executed when viewing.

**Skills Required:**
- Understanding of stored XSS
- Persistence concepts
- Multi-step attack flow

**Example Payload:**
```html
<script>alert(document.cookie)</script>
```

**Key Concepts:**
- Stored XSS persistence
- Impact assessment
- Social engineering implications

**Estimated Time:** 10-15 minutes

---

### 3. DOM XSS Lab
**Port:** 8003 | **Difficulty:** **Medium**

**Description:** DOM-based XSS where vulnerability exists in client-side JavaScript code.

**Skills Required:**
- JavaScript DOM manipulation
- Understanding of sources and sinks
- Debugging client-side code

**Example Payload:**
```javascript
#hash
<img src=x onerror=alert(1)>
```

**Key Concepts:**
- DOM XSS vs Reflected/Stored
- Sources (location.hash, location.search, etc.)
- Sinks (innerHTML, eval, etc.)
- Client-side filtering bypass

**Estimated Time:** 15-20 minutes

---

### 4. DOM innerHTML XSS Lab
**Port:** 8004 | **Difficulty:** **Medium**

**Description:** XSS via innerHTML property with some input sanitization attempts.

**Skills Required:**
- Understanding of innerHTML risks
- Attribute-based XSS
- Bypassing basic filters

**Example Payload:**
```html
<img src=x onerror=alert(1)>
<svg onload=alert(1)>
```

**Key Concepts:**
- innerHTML vs textContent
- HTML tag injection
- Event handler injection
- Filter bypass techniques

**Estimated Time:** 15-25 minutes

---

### 5. JS String XSS Lab
**Port:** 8005 | **Difficulty:** **Hard**

**Description:** XSS in JavaScript string context requiring proper string termination.

**Skills Required:**
- JavaScript syntax understanding
- String escaping
- Context-aware payloads

**Example Payload:**
```javascript
';alert(1);//
</script><script>alert(1)</script>
```

**Key Concepts:**
- Script context injection
- String termination
- JavaScript operators
- Breaking out of context

**Estimated Time:** 20-30 minutes

---

### 6. Stored XSS Href Lab
**Port:** 8006 | **Difficulty:** **Medium**

**Description:** Stored XSS via href attribute where javascript: protocol can be used.

**Skills Required:**
- Understanding of HTML attributes
- javascript: protocol
- Attribute-based injection

**Example Payload:**
```html
javascript:alert(1)
javascript://%0aalert(1)
```

**Key Concepts:**
- HTML attribute injection
- javascript: protocol
- Href-based XSS
- Event handler alternatives

**Estimated Time:** 15-20 minutes

---

### 7. JS Context XSS Lab
**Port:** 8007 | **Difficulty:** **Hard**

**Description:** XSS in complex JavaScript context with variable assignment or function calls.

**Skills Required:**
- Advanced JavaScript
- Understanding different JS contexts
- Payload obfuscation

**Example Payload:**
```javascript
;alert(1)//
\x3cimg src=x onerror=alert(1)\x3e
```

**Key Concepts:**
- Variable injection
- Function call manipulation
- Unicode encoding
- JS parsing behavior

**Estimated Time:** 25-35 minutes

---

### 8. JSON XSS Lab
**Port:** 8008 | **Difficulty:** **Hard**

**Description:** XSS via JSON parsing where input is embedded in JSON data structure.

**Skills Required:**
- JSON structure understanding
- JavaScript object parsing
- Context-aware escaping

**Example Payload:**
```json
</script><script>alert(1)</script>
<img src=x onerror=alert(1)>
```

**Key Concepts:**
- JSON parsing
- Data type manipulation
- Breaking out of JSON structure
- Script tag injection

**Estimated Time:** 25-35 minutes

---

### 9. Formaction XSS Lab
**Port:** 8009 | **Difficulty:** **Easy**

**Description:** POST-based XSS vulnerability via HTML5 formaction attribute injection on a button element.

**Skills Required:**
- Understanding of POST requests
- HTML attribute context
- HTML5 formaction attribute
- Breaking out of attribute values

**Example Payload:**
```html
"><button formaction="javascript:alert(document.cookie)">Click me
```

**Key Concepts:**
- POST-based XSS (different from GET)
- Attribute injection and escaping
- HTML5 formaction attribute
- javascript: URI in formaction
- User-triggered execution (requires click)

**Estimated Time:** 10-15 minutes

---

## Recommended Learning Path

### Complete Beginner
1. **Reflected XSS Lab** (8001) - Learn basics
2. **Stored XSS Lab** (8002) - Understand persistence
3. **Formaction XSS Lab** (8009) - Learn POST-based XSS and attribute injection
4. **DOM Hash innerHTML XSS Lab** (8010) - Learn location.hash exploitation

### Intermediate
4. **DOM XSS Lab** (8003) - Client-side vulnerabilities
5. **DOM innerHTML XSS Lab** (8004) - Advanced DOM manipulation
6. **Stored XSS Href Lab** (8006) - Attribute-based injection

### Advanced
7. **JS String XSS Lab** (8005) - Script context
8. **JS Context XSS Lab** (8007) - Complex JavaScript contexts
9. **JSON XSS Lab** (8008) - Data structure injection

---

## Common Payload Techniques by Difficulty

### Beginner Payloads
```html
<script>alert(1)</script>
<img src=x onerror=alert(1)>
```

### Intermediate Payloads
```html
<svg onload=alert(1)>
<iframe src="javascript:alert(1)">
<a href="javascript:alert(1)">click</a>
```

### Advanced Payloads
```javascript
';alert(1);//
</script><img src=x onerror=alert(1)>
\x3cscript\x3ealert(1)\x3c/script\x3e
javascript://%0aalert(1)//%0d
```

### Expert Payloads
```javascript
${alert(1)}
\u003cimg src=x onerror=alert(1)\u003e
<details open ontoggle=alert(1)>
```

---

## Tips for Each Difficulty Level

### Beginner Tips
- Start with simple `<script>` tags
- Use browser DevTools to inspect the page
- Look for unfiltered input reflection

### Easy Tips
- Try different HTML tags (img, svg, body)
- Test various event handlers (onload, onerror, onclick)
- Check if HTML entities are being used

### Medium Tips
- Understand the context (HTML, attribute, JavaScript)
- Use appropriate escaping for each context
- Test with different injection points

### Hard Tips
- Analyze the exact JavaScript context
- Use proper syntax to break out of strings
- Consider Unicode and hex encoding

### Expert Tips
- Think outside the box with edge cases
- Use browser-specific features
- Combine multiple techniques
- Obfuscate payloads when needed

---

## Completion Checklist

Track your progress:

- [ ] Reflected XSS Lab (8001) - Easy
- [ ] Stored XSS Lab (8002) - Easy
- [ ] Formaction XSS Lab (8009) - Easy
- [ ] DOM Hash innerHTML XSS Lab (8010) - Easy
- [ ] DOM XSS Lab (8003) - Medium
- [ ] DOM innerHTML XSS Lab (8004) - Medium
- [ ] JS String XSS Lab (8005) - Hard
- [ ] Stored XSS Href Lab (8006) - Medium
- [ ] JS Context XSS Lab (8007) - Hard
- [ ] JSON XSS Lab (8008) - Hard

---

## Additional Resources

- [OWASP XSS Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [PortSwigger Web Security Academy](https://portswigger.net/web-security/cross-site-scripting)
- [DOM-based XSS Guide](https://domgo.github.io/cxq/)

---

## Notes

- Difficulty ratings are subjective and may vary based on prior experience
- Some labs may have multiple solutions with different difficulty levels
- Always try to find the most elegant solution
- Focus on understanding why a payload works, not just that it works

Last updated: 2025-02-08
