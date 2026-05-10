#!/bin/bash

# SQL Injection Error-Based Lab Stop Script

echo "Stopping Error-Based SQL Injection Lab..."

# Try docker-compose first
if [ -f "docker-compose.yml" ]; then
    if command -v docker-compose &> /dev/null; then
        docker-compose down
    elif docker compose version &> /dev/null; then
        docker compose down
    fi
fi

# Also try stopping by name
if docker ps -q --filter "name=sqli-lab-error" | grep -q .; then
    docker stop sqli-lab-error
    docker rm sqli-lab-error
fi

echo "Lab stopped."
