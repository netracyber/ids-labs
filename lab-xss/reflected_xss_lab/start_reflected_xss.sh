#!/bin/bash

echo "Starting Reflected XSS Lab Server..."
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server for reflected XSS with router
php -S 0.0.0.0:8001 -t /root/tools/lab-xss/reflected_xss_lab /root/tools/lab-xss/reflected_xss_lab/reflected_router.php