#!/bin/bash

# Check if user is in docker group
if ! groups | grep -q docker; then
    echo "You need to be in the 'docker' group or run with sudo."
    echo "Run: sudo usermod -aG docker \$USER"
    echo "Then log out and log back in."
    echo ""
    echo "Or run with sudo: sudo ./scripts/stop_docker_labs.sh"
    exit 1
fi

echo "=========================================="
echo "  Stopping XSS Labs Docker Containers"
echo "=========================================="
echo ""

# Stop all containers
docker compose down

echo ""
echo "All XSS lab containers stopped!"
echo ""
echo "To remove images as well, run:"
echo "  docker compose down --rmi all"
echo ""
echo "To remove everything including volumes, run:"
echo "  docker compose down -v --rmi all"
