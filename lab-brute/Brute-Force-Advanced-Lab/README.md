# CTF Lab - Advanced Brute Force Challenge

## Overview
This CTF lab is designed to demonstrate advanced brute force techniques against a rate-limited login form. The application implements rate limiting to prevent simple brute force attacks, requiring more sophisticated approaches.

## Directory Structure
- `src/` - Contains the source code and templates
- `docs/` - Contains documentation
- `exploits/` - Contains wordlists and brute force scripts
- `config/` - Contains configuration files
- `scripts/` - Contains utility scripts

## Setup
1. Make sure you have Python 3 and Flask installed
2. Run the application from the scripts directory: `cd scripts && ./start.sh`
3. The application will be available at `http://localhost:6004`

## Challenge
- Username: `admin`
- The password is in the wordlist provided
- Bypass the rate limiting (max 3 attempts per 10 seconds per IP)
- Use advanced brute force techniques to find the password

## Techniques to Try
- Time-delayed brute forcing
- IP rotation using proxies
- Session-based attacks
- Using the API endpoint at /api/status to gather information

## Files
- `src/app.py`: Main Flask application with rate limiting
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: Detailed documentation
- `exploits/wordlist.txt`: Wordlist for brute force
- `exploits/brute_force_script.py`: Advanced brute force script
- `scripts/start.sh`: Startup script to run the application
- `config/requirements.txt`: Python dependencies
