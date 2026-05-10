#!/bin/bash

echo "Starting Stored XSS Href Lab Server..."
echo "================================"
echo "Lab URL: http://0.0.0.0:8006 (accessible via server's IP address)"
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
php -S 0.0.0.0:8006