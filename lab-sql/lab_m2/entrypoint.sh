#!/bin/bash
sleep 5
python3 /app/generate_flag.py
FLAG=$(cat /tmp/flag.txt)
mysql -h lab_m2_db -uroot -proot lab -e "UPDATE flags SET flag='$FLAG' WHERE id=1;"
apache2-foreground
