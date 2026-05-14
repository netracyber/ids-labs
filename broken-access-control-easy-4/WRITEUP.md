# Broken Access Control Easy 4 - Forceful Browsing
## Port: 8078

### Vulnerability
The debug endpoint is reachable by forceful browsing because it has no auth check.

### Intended exploit
Visit /api/admin/debug/flag directly. No login or special headers are needed.
