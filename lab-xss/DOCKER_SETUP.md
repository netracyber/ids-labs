# XSS Labs Docker Setup

## Overview

All XSS labs have been configured to run in Docker containers on ports 8020-8029.

## Port Mapping

| Setup | Ports | Status |
|-------|-------|--------|
| **Manual PHP Labs** | 8001-8009 | Running (original setup) |
| **Docker Labs** | 8020-8029 | Ready to start |

## Prerequisites

1. **Docker** must be installed
2. **User in docker group** (to run without sudo)

### Add user to docker group (one-time setup)

```bash
sudo usermod -aG docker $USER
```

Then log out and log back in for changes to take effect.

## Available Docker Labs

| Port | Lab Name | Difficulty | Description |
|------|----------|------------|-------------|
| 8020 | Reflected XSS Lab | Easy | Classic reflected XSS vulnerability |
| 8021 | Stored XSS Lab | Easy | Stored XSS in blog comments |
| 8022 | DOM XSS Lab | Medium | DOM-based XSS vulnerability |
| 8023 | DOM innerHTML XSS Lab | Medium | XSS via innerHTML manipulation |
| 8024 | JS String XSS Lab | Hard | JavaScript string context XSS |
| 8025 | Stored XSS Href Lab | Medium | Stored XSS via href attribute |
| 8026 | JS Context XSS Lab | Hard | JavaScript context XSS |
| 8027 | JSON XSS Lab | Hard | JSON-based XSS |
| 8028 | Formaction XSS Lab | Easy | POST-based XSS via formaction attribute |
| 8029 | DOM Hash innerHTML XSS Lab | Easy | DOM-based XSS via location.hash |

## Starting All Labs

```bash
./scripts/start_docker_labs.sh
```

Or with sudo (if not in docker group):
```bash
sudo ./scripts/start_docker_labs.sh
```

## Stopping All Labs

```bash
./scripts/stop_docker_labs.sh
```

Or with sudo:
```bash
sudo ./scripts/stop_docker_labs.sh
```

## Individual Lab Management

### Start specific lab:
```bash
docker compose up -d reflected-xss-lab
```

### Stop specific lab:
```bash
docker compose stop reflected-xss-lab
```

### View logs:
```bash
docker compose logs -f reflected-xss-lab
```

### Restart lab:
```bash
docker compose restart reflected-xss-lab
```

## Accessing Labs

Once started, all labs are accessible at `http://localhost:<port>/` or `http://72.61.140.122:<port>/`

### Docker Labs (8020-8029)
- Reflected XSS: http://localhost:8020/
- Stored XSS: http://localhost:8021/
- DOM XSS: http://localhost:8022/
- DOM innerHTML XSS: http://localhost:8023/
- JS String XSS: http://localhost:8024/
- Stored XSS Href: http://localhost:8025/
- JS Context XSS: http://localhost:8026/
- JSON XSS: http://localhost:8027/
- Formaction XSS: http://localhost:8028/
- DOM Hash innerHTML XSS: http://localhost:8029/

### Manual Labs (8001-8009) - Still Running
- Reflected XSS: http://localhost:8001/
- Stored XSS: http://localhost:8002/
- DOM XSS: http://localhost:8003/
- DOM innerHTML XSS: http://localhost:8004/
- JS String XSS: http://localhost:8005/
- Stored XSS Href: http://localhost:8006/
- JS Context XSS: http://localhost:8007/
- JSON XSS: http://localhost:8008/

## Why Two Setups?

The manual PHP labs (8001-8009) were the original setup and are still running. The Docker labs (8020-8029) provide:
- Better isolation
- Easier management
- Consistent environment
- Simple start/stop

You can use either setup, or both simultaneously!

## Troubleshooting

### Permission denied errors

If you get "permission denied" errors:
1. Add yourself to docker group: `sudo usermod -aG docker $USER`
2. Log out and log back in
3. Or run commands with sudo

### Port already in use

If a port is already in use, check what's using it:
```bash
netstat -tuln | grep :8020
```

Stop the conflicting service or change the port in `docker-compose.yml`.

### View running containers
```bash
docker ps
```

### View all containers (including stopped)
```bash
docker ps -a
```

### Clean up everything
```bash
docker compose down -v --rmi all
```
