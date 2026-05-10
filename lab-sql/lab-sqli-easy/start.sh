#!/bin/bash

echo "=========================================="
echo "PostgreSQL Error-Based SQL Injection Lab"
echo "=========================================="
echo ""

# Build and start Docker containers
echo "Building Docker image..."
docker-compose build

echo ""
echo "Starting containers..."
docker-compose up -d

echo ""
echo "Waiting for services to be ready..."
sleep 5

# Get the assigned port
PORT=$(docker-compose port app 8080 | cut -d: -f2)

echo ""
echo "=========================================="
echo "Lab is running!"
echo "=========================================="
echo "Access URL: http://localhost:$PORT"
echo ""
echo "To view logs: docker-compose logs -f"
echo "To stop: ./stop.sh"
echo "=========================================="
