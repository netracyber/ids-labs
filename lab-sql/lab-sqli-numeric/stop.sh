#!/bin/bash

# SQL Injection Numeric Lab Stop Script

echo "Stopping Numeric SQL Injection Lab..."

# Try docker-compose first
if [ -f "docker-compose.yml" ]; then
    if command -v docker-compose &> /dev/null; then
        docker-compose down
    elif docker compose version &> /dev/null; then
        docker compose down
    fi
fi

# Also try stopping by name
if docker ps -q --filter "name=sqli-lab-numeric" | grep -q .; then
    docker stop sqli-lab-numeric
    docker rm sqli-lab-numeric
fi

echo "Lab stopped."
