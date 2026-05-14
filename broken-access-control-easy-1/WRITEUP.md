# Broken Access Control Easy 1 - IDOR
## Port: 8075

### Vulnerability
The endpoint trusts the path ID and returns another user's private data when the attacker changes it.

### Intended exploit
Login as alice, then request /api/users/2 and read the response flag.
