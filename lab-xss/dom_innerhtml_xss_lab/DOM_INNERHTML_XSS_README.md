# DOM XSS in innerHTML Sink using Source location.search - Lab

## Lab Overview
This lab contains a DOM-based cross-site scripting vulnerability in the search blog functionality. It uses an innerHTML assignment, which changes the HTML contents of a div element, using data from location.search.

## Vulnerability Details
The vulnerability exists in the JavaScript code where user input from `location.search` is directly assigned to an element's `innerHTML` property without proper sanitization. This allows an attacker to inject malicious HTML and JavaScript code.

## Lab Objective
Find and exploit the XSS vulnerability in this blog application.

## Technical Details
- **Type**: DOM-based XSS
- **Sink**: innerHTML assignment
- **Source**: location.search
- **Location**: JavaScript code in dom_innerhtml_xss.html
- **Flag**: `IDS{e0b37cb9c327bc8a741bf11e6cd88025}`

## Setup Instructions
1. Navigate to the lab directory: `cd /root/tools/lab-xss/dom_innerhtml_xss_lab`
2. Start the PHP server: `php -S 0.0.0.0:8004`
3. Access the lab in your browser at `http://[SERVER_IP]:8004/`