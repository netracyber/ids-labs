#!/bin/bash

echo "=========================================="
echo "  DOM Hash XSS Lab - Starting"
echo "=========================================="
echo ""
echo "Lab Details:"
echo "  Name:        DOM Hash XSS Lab"
echo "  Difficulty:  Easy"
echo "  Port:        8019"
echo "  Type:        DOM-based XSS via location.hash"
echo ""
echo "Access the lab at:"
echo "  http://localhost:8019/"
echo ""
echo "Quick test URL:"
echo "  http://localhost:8019/#<img src=x onerror=\"alert('XSS_SUCCESS')\">"
echo ""
echo "Press Ctrl+C to stop the server"
echo "=========================================="
echo ""

# Start PHP built-in server
php -S 0.0.0.0:8019
