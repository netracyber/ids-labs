# API Security Easy 3 - IDOR
## Port: 8072

### Vulnerability
The endpoint trusts the path ID and never checks whether the logged-in user owns that record.

### Intended exploit
Login as alice, then request /api/users/2. The flag appears when you access another user's record.
