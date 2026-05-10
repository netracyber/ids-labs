# CTF Labs - Complete POC Guide

This document provides proof of concept (POC) for each of the CTF labs created, showing how to successfully exploit them to get the flags.

## Lab 1: Brute Force Password Challenge

**Location:** /root/tools/Brute-Force-Password-Challenge
**Port:** 6001
**Flag:** IDS{6400193b3440aa70d02f525df097e254}

### POC:
1. Navigate to the lab directory:
   ```bash
   cd /root/tools/Brute-Force-Password-Challenge
   ```
2. Start the application:
   ```bash
   cd scripts && ./start.sh
   ```
3. The application will be available at http://localhost:6001
4. Username is 'admin' (pre-filled in the login form)
5. Password is 'password' (SHA-256 hash: 5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8)
6. Login to get the flag: IDS{6400193b3440aa70d02f525df097e254}

## Lab 2: Hashcat and John the Ripper Challenge

**Location:** /root/tools/Hashcat-John-Brute-Force-Lab
**Port:** 6002
**Flag:** IDS{80a0e8b19352eca73db090b0e27b0d32}

### POC:
1. Navigate to the lab directory:
   ```bash
   cd /root/tools/Hashcat-John-Brute-Force-Lab
   ```
2. Start the application:
   ```bash
   cd scripts && ./start.sh
   ```
3. The application will be available at http://localhost:6002
4. The hash is stored in exploits/password.hash: 5858ea228cc2edf88721699b2c8638e5 (MD5 hash of "welcome123"). A 100-password wordlist is available at /download-wordlist from the application
5. Use hashcat to crack it:
   ```bash
   hashcat -m 0 exploits/password.hash exploits/wordlist_exactly_100.txt
   ```
6. Or use John the Ripper:
   ```bash
   john --format=raw-md5 --wordlist=exploits/wordlist_exactly_100.txt exploits/password.hash
   ```
7. Login with username 'admin' and password 'welcome123' to get the flag: IDS{80a0e8b19352eca73db090b0e27b0d32}

## Lab 3: SQL Injection Challenge

**Location:** /root/tools/SQL-Injection-Lab
**Port:** 6003
**Flag:** IDS{5ql1nj3ct10n_1s_d4ng3r0us}

### POC:
1. Navigate to the lab directory:
   ```bash
   cd /root/tools/SQL-Injection-Lab
   ```
2. Start the application:
   ```bash
   cd scripts && ./start.sh
   ```
3. The application will be available at http://localhost:6003
4. Use authentication bypass with SQL injection:
   - Username: flag'--
   - Password: anything
5. Or use the automated exploit script:
   ```bash
   python3 exploits/sql_injection_exploit.py
   ```
6. Login to get the flag: IDS{5ql1nj3ct10n_1s_d4ng3r0us}

## Lab 4: Advanced Brute Force Challenge

**Location:** /root/tools/Brute-Force-Advanced-Lab
**Port:** 6004
**Flag:** IDS{1160eb3fa6e97d6b9fcfc643b3998021}

### POC:
1. Navigate to the lab directory:
   ```bash
   cd /root/tools/Brute-Force-Advanced-Lab
   ```
2. Start the application:
   ```bash
   cd scripts && ./start.sh
   ```
3. The application will be available at http://localhost:6004
4. The application has rate limiting: max 3 attempts per 10 seconds per IP
5. Use the brute force script to bypass rate limiting:
   ```bash
   python3 exploits/brute_force_script.py
   ```
6. Or try the correct password directly: 'securepass123'
7. Login to get the flag: IDS{1160eb3fa6e97d6b9fcfc643b3998021}
