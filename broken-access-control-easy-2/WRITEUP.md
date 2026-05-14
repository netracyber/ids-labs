# Broken Access Control Easy 2 - Horizontal Privilege Escalation
## Port: 8076

### Vulnerability
The update endpoint never checks ownership, so a logged-in user can modify somebody else's profile.

### Intended exploit
Login as alice, then PUT /api/users/2 with a JSON payload to trigger the flag.
