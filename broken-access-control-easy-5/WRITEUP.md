# Broken Access Control Easy 5 - Path Bypass
## Port: 8079

### Vulnerability
The application exposes a sibling JSON route that skips the normal admin gate.

### Intended exploit
Browse to /api/admin/flag.json. That alternate path returns the flag without authentication.
