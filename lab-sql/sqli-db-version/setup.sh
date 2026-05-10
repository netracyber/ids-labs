#!/bin/bash

# Script setup for SQL Injection Lab - Database Version Query
# Created for IDS CyberSec Academy

echo "==========================================="
echo "SQL Injection Lab - Database Version Query Setup"
echo "==========================================="

# Check if Docker is installed
if ! [ -x "$(command -v docker)" ]; then
  echo "Error: Docker not found. Please install Docker first." >&2
  exit 1
fi

# Check if docker compose is installed
if ! [ -x "$(command -v docker compose)" ]; then
  echo "Error: Docker Compose not found. Please install Docker Compose first." >&2
  exit 1
fi

echo "All dependencies found. Proceeding with setup..."

# Build and run the application
echo "Building and running application..."
docker compose up -d --build

# Wait a few seconds for the application to be ready
echo "Waiting for application to be ready..."
sleep 10

# Check if the application is running
if curl -s http://localhost:5006 > /dev/null; then
    echo "==========================================="
    echo "Application successfully launched!"
    echo "Access: http://localhost:5006"
    echo "==========================================="
    echo ""
    echo "Lab Instructions:"
    echo "- This application has a SQL injection vulnerability in the product category filter"
    echo "- You can use a UNION attack to retrieve the results from an injected query"
    echo "- Your goal is to display the database version string"
    echo ""
    echo "To solve the lab:"
    echo "1. Go to http://localhost:5006 in your browser"
    echo "2. Select a category from the dropdown or manipulate the URL parameter"
    echo "3. Use SQL injection with UNION to query database version"
    echo "   For MySQL: Try payloads like: ' UNION SELECT 1,@@version,3,4,5,6 --"
    echo "   For SQL Server: Try payloads like: ' UNION SELECT 1,@@version,3,4,5,6 --"
    echo "4. When you successfully display the database version, the flag will appear"
    echo "   Flag: IDS{4c24a70d8e6436cb7bc3c986d54d7723}"
    echo ""
    echo "==========================================="
else
    echo "There was an issue running the application. Please check logs with command:"
    echo "docker compose logs"
fi

# Show container status
echo ""
echo "Container status:"
docker ps --filter "name=sqli-db-version-sqli-db-version-1"