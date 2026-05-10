# CTF Lab - Brute Force Password Challenge

## Overview
This CTF lab is designed to demonstrate a brute force password attack vulnerability. The application has a login page with a weak password that can be easily brute forced.

## Setup
1. Make sure you have Python 3 and Flask installed
2. Run the application: `cd scripts && ./start.sh`
3. The application will be available at `http://localhost:6001`

## Challenge
- Username: `admin`
- The password is a common, weak password that can be brute forced
- Use a brute force tool or script to find the password
- Once logged in, you'll see a welcome message and the flag

## Solution
The password for the 'admin' user is 'password' (hashed as SHA-256: 5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8)

## Flag
After successful login, you will see the flag: `IDS{6400193b3440aa70d02f525df097e254}`

## Security Notes
This application demonstrates the importance of:
- Using strong, unique passwords
- Implementing account lockout mechanisms after failed attempts
- Using proper password hashing with salt
- Avoiding common passwords

## Files
- `src/app.py`: Main Flask application
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: This documentation file
- `exploits/brute_force.py`: Brute force script example
- `exploits/passwords.txt`: Common passwords wordlist
- `scripts/start.sh`: Startup script to run the application
- `config/requirements.txt`: Python dependencies
