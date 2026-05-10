#!/bin/bash
# Script to start the Hashcat/John the Ripper CTF lab application

echo "Starting Hashcat/John the Ripper CTF Lab..."
echo "Application will be available at http://localhost:6002"

cd ../src
python3 app.py
