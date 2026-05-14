# API Security Easy 1 - Broken Authentication
## Port: 8070

### Vulnerability
The admin users endpoint has no authentication check and returns the flag directly.

### Intended exploit
Request GET /api/admin/users from the browser or with curl. The flag is in the secret field.
