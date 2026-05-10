# Web Security Academy XSS Labs

This repository contains three separate cross-site scripting (XSS) labs that demonstrate different types of XSS vulnerabilities, each running on its own port:

## 1. Reflected XSS Lab - HTML Context (No Encoding)

This lab demonstrates a reflected cross-site scripting vulnerability where user input is directly reflected in the HTML context without any encoding.

### Vulnerability Details
The vulnerability exists in the search functionality where the user's search term is directly reflected in the HTML without proper encoding. The PHP code in `search.php` takes the `search` parameter from the GET request and directly echoes it to the page without sanitization.

### Solution
To exploit this vulnerability and solve the lab:
1. Go to the search page
2. Enter the following payload in the search box:
   ```html
   <script>alert('XSS')</script>
   ```
3. Submit the search
4. The alert should execute, demonstrating the XSS vulnerability

### Setup Instructions
1. Install PHP (if not already installed):
   ```bash
   sudo apt update
   sudo apt install php php-cli
   ```

2. Start the Reflected XSS lab server:
   ```bash
   cd /root/tools/lab-xss/reflected_xss_lab
   ./start_reflected_xss.sh
   ```

3. Access the lab in your browser at `http://[SERVER_IP]:8001`

## 2. Stored XSS Lab - HTML Context (No Encoding)

This lab demonstrates a stored cross-site scripting vulnerability where user input is stored and then reflected in the HTML context without any encoding.

### Vulnerability Details
The vulnerability exists in the comment functionality where user comments are stored without sanitization and then displayed directly in the HTML without proper encoding. The PHP code stores user comments and displays them without any sanitization.

### Solution
To exploit this vulnerability and solve the lab:
1. Go to the blog post page
2. Enter the following payload in the comment box:
   ```html
   <script>alert('XSS')</script>
   ```
3. Submit the comment
4. The JavaScript code will be stored and executed when the page is viewed again

### Setup Instructions
1. Install PHP (if not already installed):
   ```bash
   sudo apt update
   sudo apt install php php-cli
   ```

2. Start the Stored XSS lab server:
   ```bash
   cd /root/tools/lab-xss/stored_xss_lab
   ./start_stored_xss.sh
   ```

3. Access the lab in your browser at `http://[SERVER_IP]:8002`

## 3. DOM XSS Lab - document.write with location.search

This lab demonstrates a DOM-based cross-site scripting vulnerability that uses the JavaScript `document.write` function with data from `location.search`.

### Vulnerability Details
The vulnerability exists in the JavaScript code that uses `document.write` with data from `location.search`, which can be controlled via the URL. The vulnerable code takes the `search` parameter from the URL and directly writes it to the page.

### Solution
To exploit this vulnerability and solve the lab:
1. Navigate to the DOM XSS page with a payload in the search parameter:
   ```
   http://[SERVER_IP]:8003/dom_xss.html?search=<script>alert('XSS')</script>
   ```
2. The JavaScript code will execute the payload via `document.write`

### Setup Instructions
1. Install PHP (if not already installed):
   ```bash
   sudo apt update
   sudo apt install php php-cli
   ```

2. Start the DOM XSS lab server:
   ```bash
   cd /root/tools/lab-xss/dom_xss_lab
   ./start_dom_xss.sh
   ```

3. Access the lab in your browser at `http://[SERVER_IP]:8003/dom_xss.html`

## Flags

- Reflected XSS Lab: `IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}`
- Stored XSS Lab: `IDS{1c8a5c15517d898e873a11dd32a19fa4}`
- DOM XSS Lab: `IDS{6326ea06ab28fe9c08cd27189395a62e}`

## Directory Structure

The repository is organized into separate directories for each lab:

```
lab-xss/
├── reflected_xss_lab/
│   ├── reflected_xss_index.html
│   ├── search.php
│   ├── reflected_router.php
│   ├── start_reflected_xss.sh
│   └── REFLECTED_XSS_README.md
├── stored_xss_lab/
│   ├── blog_post.php
│   ├── stored_xss_index.html
│   ├── stored_router.php
│   ├── start_stored_xss.sh
│   ├── STORED_XSS_README.md
│   ├── STORED_XSS_SOLUTION.md
│   └── STORED_XSS_DETAILED_SOLUTION.md
├── dom_xss_lab/
│   ├── dom_xss.html
│   ├── start_dom_xss.sh
│   ├── DOM_XSS_README.md
│   └── DOM_XSS_SOLUTION.md
└── README.md
```

## Security Concepts Demonstrated

- Reflected XSS
- Stored XSS
- DOM-based XSS
- HTML context injection
- JavaScript context injection
- Lack of input sanitization
- Lack of output encoding
- Client-side scripting vulnerability

## Additional Documentation

- **[XSS Lab Difficulty Ratings](XSS_LAB_DIFFICULTY_RATINGS.md)** - Detailed difficulty ratings and recommended learning path for all 8 labs
- **[Docker Setup Guide](DOCKER_SETUP.md)** - Instructions for running all labs in Docker containers