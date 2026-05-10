#!/bin/bash

# SQL Injection Labs Deployment Script
# Author: IDS - CyberSec Academy

echo "========================================"
echo "  SQL Injection Labs - Deployment"
echo "========================================"

# Create network if not exists
echo "[1/3] Creating Docker network..."
docker network create ctf_net 2>/dev/null || echo "Network ctf_net already exists"

# Start all labs
echo "[2/3] Starting all lab containers..."

# Easy Labs (E1-E6)
for i in 1 2 3 4 5 6; do
    echo "  Starting lab_e$i..."
    cd /home/labuser/tools/lab-sql/lab_e$i
    docker compose up -d --build 2>/dev/null
done

# Medium Labs (M1-M5)
for i in 1 2 3 4 5; do
    echo "  Starting lab_m$i..."
    cd /home/labuser/tools/lab-sql/lab_m$i
    docker compose up -d --build 2>/dev/null
done

# Hard Labs (H1-H5)
for i in 1 2 3 4 5; do
    echo "  Starting lab_h$i..."
    cd /home/labuser/tools/lab-sql/lab_h$i
    docker compose up -d --build 2>/dev/null
done

# Start Nginx
echo "[3/3] Starting Nginx reverse proxy..."
cd /home/labuser/tools/lab-sql/nginx
docker compose up -d

echo ""
echo "========================================"
echo "  Deployment Complete!"
echo "========================================"
echo ""
echo "Available Labs:"
echo "  Easy:   /e1/ to /e6/"
echo "  Medium: /m1/ to /m5/"
echo "  Hard:   /h1/ to /h5/"
echo ""
echo "Access via: http://localhost/e1/search.php?q=test"
echo ""
