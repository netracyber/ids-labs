# API Security Easy 5 - JWT Token Tampering
## Port: 8074

### Vulnerability
The server accepts a forged token payload and grants admin access when the role is changed to admin.

### Intended exploit
Login, base64-edit the middle JWT segment so role=admin, then call GET /api/admin/flag.
