# CTF Brute Force Labs - Rating & Level Guide

## Overview

This document provides the rating and difficulty level for each brute force lab in the CTF training environment.

---

## Lab Summary Table

| Lab Name | Level | Difficulty | Points | Port | Est. Time |
|----------|-------|------------|--------|------|-----------|
| Brute Force Password Challenge | Beginner | 2/10 | 100 | 6001 | 15-30 min |
| Hashcat John Brute Force Lab | Intermediate | 5/10 | 250 | 6002 | 30-60 min |
| Brute Force Advanced Lab | Advanced | 8/10 | 500 | 6004 | 60-120 min |

---

## Lab 1: Brute Force Password Challenge

**File:** `Brute-Force-Password-Challenge/rating.json`

### Rating Details
- **Level:** Beginner
- **Difficulty:** 2/10
- **Points:** 100
- **Port:** 6001
- **Estimated Time:** 15-30 minutes

### Skills Required
- Basic HTTP Requests (Beginner)
- Hash Identification (Beginner)
- Dictionary Attack (Beginner)
- SHA-256 Hashing (Beginner)

### Learning Objectives
- Understand basic password hashing
- Learn how to perform dictionary attacks
- Identify SHA-256 hashes
- Use tools like Hydra or custom scripts

### Challenges Breakdown
1. Find Login Page - 10 points
2. Identify Hash Type - 20 points
3. Brute Force Password - 70 points

**Total Points:** 100

**Credentials:** admin/password (SHA-256)

---

## Lab 2: Hashcat John Brute Force Lab

**File:** `Hashcat-John-Brute-Force-Lab/rating.json`

### Rating Details
- **Level:** Intermediate
- **Difficulty:** 5/10
- **Points:** 250
- **Port:** 6002
- **Estimated Time:** 30-60 minutes

### Skills Required
- MD5 Hash Cracking (Intermediate)
- Hashcat Tool (Intermediate)
- John the Ripper (Intermediate)
- Directory Enumeration (Beginner)
- HTTP Header Analysis (Beginner)

### Learning Objectives
- Master MD5 hash cracking techniques
- Learn Hashcat syntax and options
- Learn John the Ripper basics
- Discover hidden endpoints
- Understand HTTP header manipulation

### Challenges Breakdown
1. Enumerate Application - 30 points
2. Discover Hash Endpoint - 40 points
3. Download Wordlist - 30 points
4. Crack MD5 Hash - 150 points

**Total Points:** 250

**Credentials:** admin/welcome123 (MD5 hash)

---

## Lab 3: Brute Force Advanced Lab

**File:** `Brute-Force-Advanced-Lab/rating.json`

### Rating Details
- **Level:** Advanced
- **Difficulty:** 8/10
- **Points:** 500
- **Port:** 6004
- **Estimated Time:** 60-120 minutes
- **Attempts Allowed:** 3 per IP (rate limited)

### Skills Required
- Advanced Brute Force Techniques (Advanced)
- Rate Limit Bypass (Advanced)
- Distributed Attacks (Intermediate)
- Timing Analysis (Intermediate)
- Smart Dictionary Attacks (Advanced)
- Request Optimization (Advanced)

### Learning Objectives
- Understand rate limiting mechanisms
- Learn techniques to bypass rate limits
- Implement distributed brute force attacks
- Optimize attack efficiency
- Handle timing-based restrictions
- Create targeted wordlists

### Challenges Breakdown
1. Analyze Rate Limiting - 75 points
2. Bypass Simple Rate Limit - 125 points
3. Optimize Attack Strategy - 150 points
4. Successful Brute Force - 150 points

**Total Points:** 500

**Credentials:** admin/securepass123 (rate limited)

---

## Progression Path

```
Beginner (Lab 1)
       ↓
Intermediate (Lab 2)
       ↓
Advanced (Lab 3)
```

### Recommended Completion Order

1. **Start with Lab 1** - Learn basic concepts without complexity
2. **Move to Lab 2** - Add tool usage and enumeration skills
3. **Finish with Lab 3** - Apply advanced techniques under constraints

---

## Total Points Possible

| Completion | Points |
|------------|--------|
| Lab 1 Only | 100 |
| Lab 1 + Lab 2 | 350 |
| All Labs | 850 |

---

## Difficulty Metrics

### Lab 1 - Beginner (2/10)
- No rate limiting
- Common password
- Straightforward hash type
- Basic tools sufficient

### Lab 2 - Intermediate (5/10)
- Requires enumeration
- Hidden endpoints
- Multiple tools needed
- Wordlist analysis required

### Lab 3 - Advanced (8/10)
- Strict rate limiting
- Requires strategy over speed
- Countermeasures in place
- Multiple techniques needed

---

## Hint System

Each lab includes hints with point deductions:

| Lab | Hint Levels | Cost Range |
|-----|-------------|------------|
| Lab 1 | 3 hints | 10-30 points |
| Lab 2 | 4 hints | 25-60 points |
| Lab 3 | 4 hints | 50-125 points |

---

## Prerequisites Summary

### Before Starting Lab 1
- Basic Python knowledge
- Understanding of HTTP
- Command line familiarity

### Before Starting Lab 2
- Complete Lab 1
- Hash cracking fundamentals
- Web enumeration basics

### Before Starting Lab 3
- Complete Labs 1 & 2
- Strong Python skills
- Multi-threading concepts
- Network understanding

---

## Usage

To view individual lab ratings:

```bash
# Lab 1
cat /home/labuser/tools/lab-brute/Brute-Force-Password-Challenge/rating.json | jq

# Lab 2
cat /home/labuser/tools/lab-brute/Hashcat-John-Brute-Force-Lab/rating.json | jq

# Lab 3
cat /home/labuser/tools/lab-brute/Brute-Force-Advanced-Lab/rating.json | jq
```

Or use Python:

```python
import json

# Load lab rating
with open('Brute-Force-Password-Challenge/rating.json') as f:
    rating = json.load(f)
    print(f"Level: {rating['rating']['level']}")
    print(f"Points: {rating['rating']['points']}")
    print(f"Difficulty: {rating['rating']['difficulty']}/10")
```

---

## Metadata

- **Author:** CTF Lab Creator
- **Version:** 1.0
- **Category:** Brute Force
- **Last Updated:** 2026-02-08
