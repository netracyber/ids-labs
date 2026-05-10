#!/bin/bash

# SQL Injection UNION Lab Stop Script

echo "Stopping UNION SQL Injection Lab..."

# Try docker-compose first
if [ -f "docker-compose.yml" ]; then
    if command -v docker-compose &> /dev/null; then
        docker-compose down
    elif docker compose version &> /dev/null; then
        docker compose down
    fi
fi

# Also try stopping by name
if docker ps -q --filter "name=sqli-lab-union" | grep -q .; then
    docker stop sqli-lab-union
    docker rm sqli-lab-union
fi

echo "Lab stopped."
