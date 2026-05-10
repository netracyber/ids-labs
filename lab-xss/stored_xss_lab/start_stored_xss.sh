#!/bin/bash

echo "Starting Stored XSS Lab Server..."
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server for stored XSS with router
php -S 0.0.0.0:8002 -t /root/tools/lab-xss/stored_xss_lab /root/tools/lab-xss/stored_xss_lab/stored_router.php