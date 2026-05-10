# CTF Lab - SQL Injection Challenge

## Overview
This CTF lab is designed to demonstrate a SQL injection vulnerability in a login form. The application is vulnerable to authentication bypass and data extraction through SQL injection attacks.

## Directory Structure
- `src/` - Contains the source code and templates
- `docs/` - Contains documentation
- `exploits/` - Contains POC exploits and instructions
- `config/` - Contains configuration files
- `scripts/` - Contains utility scripts

## Setup
1. Make sure you have Python 3 and Flask installed
2. Run the application from the scripts directory: `cd scripts && ./start.sh`
3. The application will be available at `http://localhost:6003`

## Challenge
- Find and exploit the SQL injection vulnerability in the login form
- Bypass authentication to access the dashboard
- Extract the flag from the database

## Vulnerability Details
- The login form is vulnerable to SQL injection in both username and password fields
- The application uses direct string concatenation in SQL queries
- Authentication can be bypassed using payloads like: `admin'--`
- Data can be extracted using union-based injection

## POC Exploits
- Authentication bypass: Username: `admin'--`, Password: anything
- Union-based injection: Username: `' UNION SELECT 1,username,password FROM users--`, Password: x

## Files
- `src/app.py`: Main Flask application with vulnerable SQL query
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: Detailed documentation
- `exploits/sql_injection_poc.txt`: Detailed proof of concept
- `exploits/sql_injection_exploit.py`: Automated exploit script
- `scripts/start.sh`: Startup script to run the application
- `config/requirements.txt`: Python dependencies
