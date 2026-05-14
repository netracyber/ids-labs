# API Security Easy 2 - Excessive Data Exposure
## Port: 8071

### Vulnerability
The profile response exposes internal debug data, including the flag, to any authenticated user.

### Intended exploit
Login with alice / alice123, then send GET /api/profile using the returned token.
