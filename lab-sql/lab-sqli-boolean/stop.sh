#!/bin/bash

# SQL Injection Boolean-Based Lab Stop Script

echo "Stopping Boolean-Based SQL Injection Lab..."

# Try docker-compose first
if [ -f "docker-compose.yml" ]; then
    if command -v docker-compose &> /dev/null; then
        docker-compose down
    elif docker compose version &> /dev/null; then
        docker compose down
    fi
fi

# Also try stopping by name
if docker ps -q --filter "name=sqli-lab-boolean" | grep -q .; then
    docker stop sqli-lab-boolean
    docker rm sqli-lab-boolean
fi

echo "Lab stopped."
