# CTF Lab - Advanced Brute Force Challenge

## Overview
This CTF lab is designed to demonstrate advanced brute force techniques against a rate-limited login form. The application implements rate limiting to prevent simple brute force attacks, requiring more sophisticated approaches.

## Setup
1. Make sure you have Python 3 and Flask installed
2. Run the application from the scripts directory: `cd scripts && ./start.sh`
3. The application will be available at `http://localhost:6004`

## Challenge
- Username: `admin` (pre-filled in the login form)
- The password is in the wordlist provided
- Bypass the rate limiting (max 3 attempts per 10 seconds per IP)
- Use advanced brute force techniques to find the password

## Solution
The correct password is "securepass123".

Advanced techniques to bypass rate limiting:
1. Time-delayed brute forcing: Add delays between attempts
2. IP rotation: Use different IP addresses/proxies
3. Session-based attacks: Use different sessions for attempts
4. Using the API endpoint at /api/status to gather information about rate limiting

## API Endpoint
- /api/status: Returns rate limiting status information

## Flag
After successful login, you will see the flag: `IDS{1160eb3fa6e97d6b9fcfc643b3998021}`

## Security Notes
This application demonstrates the importance of:
- Proper rate limiting implementation
- Account lockout mechanisms
- Monitoring and alerting for brute force attempts
- Using CAPTCHA or other human verification methods
- Strong password policies

## Files
- `src/app.py`: Main Flask application with rate limiting
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: This documentation file
- `exploits/wordlist.txt`: Wordlist for brute force
- `exploits/brute_force_script.py`: Advanced brute force script
- `scripts/start.sh`: Startup script to run the application
- `config/requirements.txt`: Python dependencies
