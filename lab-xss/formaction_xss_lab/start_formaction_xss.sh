#!/bin/bash

echo "=========================================="
echo "  Formaction XSS Lab - Starting"
echo "=========================================="
echo ""
echo "Lab Details:"
echo "  Name:        Formaction XSS Lab"
echo "  Difficulty:  Easy"
echo "  Port:        8018"
echo "  Type:        POST-based XSS"
echo ""
echo "Access the lab at:"
echo "  http://localhost:8018/"
echo ""
echo "Press Ctrl+C to stop the server"
echo "=========================================="
echo ""

# Start PHP built-in server
php -S 0.0.0.0:8018
