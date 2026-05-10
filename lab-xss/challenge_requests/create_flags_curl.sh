# CTFd Flag Creation - cURL Commands

# Host: 72.61.140.122:8000
# Total Challenges: 15

# 2. Search Query XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/2' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"2","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 3. Attribute XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/3' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"3","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 4. JS String Context XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/4' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"4","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 5. Document.write XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/5' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"5","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 6. innerHTML XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/6' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"6","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 7. DOM XSS in innerHTML with location.search
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/7' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"7","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 8. Formaction XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/8' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"8","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 9. DOM Hash XSS Lab
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/9' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"9","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 10. Stored XSS Lab - HTML Context
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/10' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"10","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 11. Stored XSS in anchor href attribute
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/11' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"11","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

# 12. DOM-based XSS Lab - Document Location
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/12' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"12","content":"IDS{[a-zA-Z0-9]{20,32}}","type":"regex","data":""}'

# 13. Reflected XSS - Event Handler Attribute
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/13' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"13","content":"IDS{[a-zA-Z0-9]{20,32}}","type":"regex","data":""}'

# 14. Reflected XSS - JavaScript String Context
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/14' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"14","content":"IDS{[a-zA-Z0-9]{20,32}}","type":"regex","data":""}'

# 15. Reflected XSS - Input Filter Bypass
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/15' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"15","content":"IDS{[a-zA-Z0-9]{20,32}}","type":"regex","data":""}'

# 16. DOM XSS Lab - document.write with location.search
curl -X POST 'http://72.61.140.122:8000/api/v1/flags' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'CSRF-Token: d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee' \
  -H 'Origin: http://72.61.140.122:8000' \
  -H 'Referer: http://72.61.140.122:8000/admin/challenges/16' \
  -H 'Cookie: session=d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q' \
  --data-raw '{"challenge_id":"16","content":"IDS{[a-f0-9]{32}}","type":"regex","data":""}'

