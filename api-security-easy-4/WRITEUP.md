# API Security Easy 4 - Mass Assignment
## Port: 8073

### Vulnerability
Mass assignment lets attackers overwrite the role field and trigger the flag as admin.

### Intended exploit
Login as alice, then PUT /api/profile with a JSON body containing role=admin.
