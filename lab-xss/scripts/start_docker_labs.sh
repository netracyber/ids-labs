#!/bin/bash

# Check if user is in docker group
if ! groups | grep -q docker; then
    echo "=========================================="
    echo "  Docker Permission Required"
    echo "=========================================="
    echo ""
    echo "You need to be in the 'docker' group to run Docker without sudo."
    echo ""
    echo "Please run the following command to add yourself to the docker group:"
    echo "  sudo usermod -aG docker \$USER"
    echo ""
    echo "Then log out and log back in for the changes to take effect."
    echo ""
    echo "Alternatively, you can run this script with sudo:"
    echo "  sudo ./scripts/start_docker_labs.sh"
    echo ""
    exit 1
fi

echo "=========================================="
echo "  Starting XSS Labs in Docker Containers"
echo "=========================================="
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed!"
    exit 1
fi

# Check if any containers are already running
RUNNING=$(docker ps --filter "name=xss-lab-" --format "{{.Names}}" | wc -l)
if [ "$RUNNING" -gt 0 ]; then
    echo "Warning: Some XSS lab containers are already running!"
    echo "Run './scripts/stop_docker_labs.sh' first to stop them."
    echo ""
fi

# Start all labs
echo "Building and starting Docker containers..."
docker compose up -d

echo ""
echo "=========================================="
echo "  XSS Labs Started Successfully!"
echo "=========================================="
echo ""
echo "Lab URLs:"
echo "  1. Reflected XSS Lab:        http://localhost:8020/"
echo "  2. Stored XSS Lab:           http://localhost:8021/"
echo "  3. DOM XSS Lab:              http://localhost:8022/"
echo "  4. DOM innerHTML XSS Lab:    http://localhost:8023/"
echo "  5. JS String XSS Lab:        http://localhost:8024/"
echo "  6. Stored XSS Href Lab:      http://localhost:8025/"
echo "  7. JS Context XSS Lab:       http://localhost:8026/"
echo "  8. JSON XSS Lab:             http://localhost:8027/"
echo "  9. Formaction XSS Lab:       http://localhost:8028/"
echo " 10. DOM Hash innerHTML XSS:   http://localhost:8029/"
echo ""
echo "Old manual labs (ports 8001-8009) are still running separately."
echo ""
echo "Commands:"
echo "  View logs:     docker compose logs -f [service-name]"
echo "  Stop all labs: ./scripts/stop_docker_labs.sh"
echo "  Restart:       docker compose restart [service-name]"
echo ""
echo "Running containers:"
docker ps --filter "name=xss-lab-" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
