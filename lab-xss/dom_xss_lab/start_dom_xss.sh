#!/bin/bash

echo "Starting DOM XSS Lab Server..."
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server for DOM XSS
php -S 0.0.0.0:8003 -t /root/tools/lab-xss/dom_xss_lab