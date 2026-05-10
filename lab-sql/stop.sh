#!/bin/bash

# Stop all SQL Injection Labs

echo "Stopping all labs..."

# Easy Labs
for i in 1 2 3 4 5 6; do
    cd /home/labuser/tools/lab-sql/lab_e$i
    docker compose down 2>/dev/null
done

# Medium Labs
for i in 1 2 3 4 5; do
    cd /home/labuser/tools/lab-sql/lab_m$i
    docker compose down 2>/dev/null
done

# Hard Labs
for i in 1 2 3 4 5; do
    cd /home/labuser/tools/lab-sql/lab_h$i
    docker compose down 2>/dev/null
done

# Stop Nginx
cd /home/labuser/tools/lab-sql/nginx
docker compose down 2>/dev/null

echo "All labs stopped."
