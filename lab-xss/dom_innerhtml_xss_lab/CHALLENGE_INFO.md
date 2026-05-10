# Name:
DOM XSS in innerHTML Sink using Source location.search

## Category:
Web Security / XSS

## Message:
This challenge contains a DOM-based cross-site scripting vulnerability in the search blog functionality. It uses an innerHTML assignment, which changes the HTML contents of a div element, using data from location.search. Your task is to find and exploit the XSS vulnerability to execute JavaScript code in the victim's browser. The vulnerability allows you to inject malicious HTML and JavaScript code through the search parameter. Find a way to execute an alert function to capture the flag.