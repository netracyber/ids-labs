#!/bin/bash

# SQL Injection UNION Lab Startup Script
# This script starts the UNION SQL Injection lab container

echo "=========================================="
echo "  SQL Injection Lab - UNION Edition"
echo "  Starting Up..."
echo "=========================================="
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed!"
    echo "Please install Docker first."
    exit 1
fi

# Check if Docker Compose is available
if command -v docker-compose &> /dev/null; then
    USE_COMPOSE=true
elif docker compose version &> /dev/null; then
    USE_COMPOSE=true
else
    USE_COMPOSE=false
fi

LAB_NAME="sqli-lab-union"

# Stop any existing container with the same name
echo "Checking for existing containers..."
if docker ps -a --format '{{.Names}}' | grep -q "^${LAB_NAME}$"; then
    echo "Stopping existing container: ${LAB_NAME}"
    docker stop ${LAB_NAME} 2>/dev/null
    docker rm ${LAB_NAME} 2>/dev/null
fi

# Start the lab
echo ""
echo "Starting UNION SQL Injection Lab..."
echo ""

if [ "$USE_COMPOSE" = true ]; then
    echo "Using Docker Compose..."
    docker-compose up -d
    echo ""
    echo "Waiting for container to be ready..."
    sleep 3

    # Get the port
    CONTAINER_NAME=$(docker-compose ps -q | xargs docker inspect --format='{{.Name}}' | head -1 | sed 's/\///')
    if [ -n "$CONTAINER_NAME" ]; then
        PORT=$(docker port $CONTAINER_NAME 80 | cut -d':' -f2)
    else
        PORT=$(docker-compose ps | grep "0.0.0.0" | awk '{print $NF}' | cut -d'-' -f1)
    fi
else
    echo "Using Docker directly..."
    docker build -t ${LAB_NAME} .
    docker run -d -p 0:80 --name ${LAB_NAME} ${LAB_NAME}
    echo ""
    echo "Waiting for container to be ready..."
    sleep 3

    PORT=$(docker port ${LAB_NAME} 80 | cut -d':' -f2)
fi

if [ -n "$PORT" ]; then
    echo ""
    echo "=========================================="
    echo "  Lab is ready!"
    echo "=========================================="
    echo ""
    echo "Access the lab at:"
    echo "  http://localhost:${PORT}"
    echo ""
    echo "Challenge: Extract the flag from the hidden"
    echo "         'secret_config' table using UNION!"
    echo ""
    echo "To view logs:"
    if [ "$USE_COMPOSE" = true ]; then
        echo "  docker-compose logs -f"
    else
        echo "  docker logs -f ${LAB_NAME}"
    fi
    echo ""
    echo "To stop the lab:"
    if [ "$USE_COMPOSE" = true ]; then
        echo "  docker-compose down"
    else
        echo "  docker stop ${LAB_NAME}"
    fi
    echo ""
else
    echo ""
    echo "Error: Could not determine the assigned port."
    echo "Please check running containers with: docker ps"
fi
