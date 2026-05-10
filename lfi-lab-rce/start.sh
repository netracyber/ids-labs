#!/bin/bash
# Make /proc/self/environ readable by all (for Level 1 challenge)
# We create a copy of environ that www-data can read
mkdir -p /var/www/environ
cat /proc/1/environ > /var/www/environ/environ.txt
chmod -R 755 /var/www/environ
chmod 644 /var/www/environ/environ.txt

# Start Apache
exec apache2-foreground
