#!/bin/bash
# Script to start the SQL Injection CTF lab application

echo "Starting SQL Injection CTF Lab..."
echo "Application will be available at http://localhost:6003"

cd ../src
python3 app.py
