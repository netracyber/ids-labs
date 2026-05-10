# CTF Lab - Brute Force Password Challenge

## Overview
This CTF lab is designed to demonstrate a brute force password attack vulnerability. The application has a login page with a weak password that can be easily brute forced.

## Directory Structure
- `/src` - Contains the source code and templates
- `/docs` - Contains documentation and README
- `/exploits` - Contains exploitation scripts and wordlists
- `/config` - Contains configuration files

## Setup
1. Make sure you have Python 3 and Flask installed
2. Run the application from the src directory: `cd src && python3 app.py`
3. The application will be available at `http://localhost:6001`

## Challenge
- Username: `admin`
- The password is a common, weak password that can be brute forced
- Use a brute force tool or script to find the password
- Once logged in, you'll see a welcome message and the flag

## Files
- `src/app.py`: Main Flask application
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: Detailed documentation
- `exploits/brute_force.py`: Brute force script example
- `exploits/passwords.txt`: Common passwords wordlist