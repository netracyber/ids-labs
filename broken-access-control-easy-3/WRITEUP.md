# Broken Access Control Easy 3 - Vertical Privilege Escalation
## Port: 8077

### Vulnerability
A flawed role check grants admin-only access to any authenticated user who has a role field.

### Intended exploit
Login as alice and POST /api/admin/flag. The role field is present, so the flag is returned.
