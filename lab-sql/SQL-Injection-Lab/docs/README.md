# CTF Lab - SQL Injection Challenge

## Overview
This CTF lab is designed to demonstrate a SQL injection vulnerability in a login form. The application is vulnerable to authentication bypass and data extraction through SQL injection attacks.

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

## Solution
The flag is stored in the database as a user with username 'flag' and password 'IDS{5ql1nj3ct10n_1s_d4ng3r0us}'.

There are multiple ways to get the flag:
1. Authentication bypass: Use username `admin'--` and any password
2. Union-based injection: Use username `' UNION SELECT 1,username,password FROM users--` and any password
3. Direct flag extraction: Use username `flag'--` and any password

## POC Exploits
- Authentication bypass: Username: `admin'--`, Password: anything
- Union-based injection: Username: `' UNION SELECT 1,username,password FROM users--`, Password: x
- Flag extraction: Username: `flag'--`, Password: anything

## Flag
After successful exploitation, you will see the flag: `IDS{5ql1nj3ct10n_1s_d4ng3r0us}`

## Security Notes
This application demonstrates the importance of:
- Using parameterized queries or prepared statements
- Validating and sanitizing all user inputs
- Implementing proper error handling
- Applying the principle of least privilege for database connections
- Regular security testing and code reviews

## Files
- `src/app.py`: Main Flask application with vulnerable SQL query
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: This documentation file
- `exploits/sql_injection_poc.txt`: Detailed proof of concept
- `exploits/sql_injection_exploit.py`: Automated exploit script
- `scripts/start.sh`: Startup script to run the application
- `config/requirements.txt`: Python dependencies
