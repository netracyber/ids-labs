# CTF Lab - Hashcat and John the Ripper Challenge

## Overview
This CTF lab is designed to demonstrate password hash cracking using hashcat or John the Ripper. The application has a login page, but the password is not easily guessable. You'll need to extract the hash and crack it using specialized tools.

## Setup
1. Make sure you have Python 3 and Flask installed
2. Run the application from the scripts directory: `cd scripts && ./start.sh`
3. The application will be available at `http://localhost:6002`

## Challenge
- Username: `admin` (pre-filled in the login form)
- The password hash is stored in `exploits/password.hash`
- Use hashcat or John the Ripper to crack the MD5 hash
- Once you find the password, log in to get the flag

## Solution
The MD5 hash in `exploits/password.hash` is: `5858ea228cc2edf88721699b2c8638e5`
This is the MD5 hash of the password "welcome123".

## Tools Required
- hashcat: `hashcat -m 0 exploits/password.hash exploits/wordlist.txt`
- John the Ripper: `john --format=raw-md5 --wordlist=exploits/wordlist.txt exploits/password.hash`

## Flag
After successful login, you will see the flag: `IDS{80a0e8b19352eca73db090b0e27b0d32}`

## Security Notes
This application demonstrates the importance of:
- Using strong password hashing algorithms (not MD5)
- Using salt with password hashes
- Implementing proper access controls
- Not storing password hashes in easily accessible locations

## Files
- `src/app.py`: Main Flask application
- `src/templates/login.html`: Login page template
- `src/templates/dashboard.html`: Success page template
- `docs/README.md`: This documentation file
- `exploits/password.hash`: The MD5 hash to crack
- `exploits/wordlist.txt`: Wordlist for cracking
- `exploits/crack_instructions.sh`: Instructions for using hashcat and John
- `scripts/start.sh`: Startup script to run the application
- `config/requirements.txt`: Python dependencies
