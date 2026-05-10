#!/bin/bash

# Script to start the additional XSS labs on ports 8007, 8008, 8009

echo "Starting additional XSS labs..."

# Create a temporary directory for each lab to serve the specific file
mkdir -p /tmp/xss_lab_8007
mkdir -p /tmp/xss_lab_8008
mkdir -p /tmp/xss_lab_8009

# Copy each lab file to its respective directory
cp /root/tools/lab-xss/json_xss_lab.html /tmp/xss_lab_8007/index.html
cp /root/tools/lab-xss/js_context_xss_lab.html /tmp/xss_lab_8008/index.html
cp /root/tools/lab-xss/template_xss_lab.html /tmp/xss_lab_8009/index.html

# Start JSON-based XSS Lab on port 8007
cd /tmp/xss_lab_8007
{
  python3 -m http.server 8007 --bind 0.0.0.0
} > /root/tools/lab-xss/json_xss_lab.log 2>&1 &

sleep 2

# Start JavaScript Context XSS Lab on port 8008
cd /tmp/xss_lab_8008
{
  python3 -m http.server 8008 --bind 0.0.0.0
} > /root/tools/lab-xss/js_context_xss_lab.log 2>&1 &

sleep 2

# Start Template Injection XSS Lab on port 8009
cd /tmp/xss_lab_8009
{
  python3 -m http.server 8009 --bind 0.0.0.0
} > /root/tools/lab-xss/template_xss_lab.log 2>&1 &

sleep 2

echo "Additional XSS labs started:"
echo "- JSON-based XSS Lab: http://localhost:8007/"
echo "- JavaScript Context XSS Lab: http://localhost:8008/"
echo "- Template Injection XSS Lab: http://localhost:8009/"

echo "Check if servers are running:"
netstat -tuln | grep -E ':800[7-9]'

# Wait a bit to ensure all servers are running
sleep 3
echo "Servers status:"
ps aux | grep -E "800[7-9]" | grep -v grep