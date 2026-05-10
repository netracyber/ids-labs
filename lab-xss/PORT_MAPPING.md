# XSS Lab Port Mapping

Complete port mapping documentation for all XSS laboratory environments.

## Overview
- **Total Labs**: 19 labs (15 main labs + 4 standalone labs)
- **Port Range**: 8020-8038
- **Base URL**: `http://localhost:<PORT>`

---

## Main Labs (docker-compose.yml at root)

| Port | Lab Name | Container Name | URL | Description |
|------|----------|----------------|-----|-------------|
| 8020 | Reflected XSS Lab | xss-lab-reflected | http://localhost:8020 | Reflected XSS klasik - input langsung direfleksikan ke output |
| 8021 | Stored XSS Lab | xss-lab-stored | http://localhost:8021 | Stored XSS di komentar blog - payload tersimpan di database |
| 8022 | DOM XSS Lab | xss-lab-dom | http://localhost:8022 | DOM-based XSS - eksekusi via manipulasi DOM |
| 8023 | DOM innerHTML XSS Lab | xss-lab-dom-innerhtml | http://localhost:8023 | XSS via innerHTML property - injeksi ke elemen HTML |
| 8024 | JS String XSS Lab | xss-lab-js-string | http://localhost:8024 | JavaScript string context - XSS dalam string JavaScript |
| 8025 | Stored XSS Href Lab | xss-lab-stored-href | http://localhost:8025 | Stored XSS via href attribute - injeksi pada link href |
| 8026 | JS Context XSS Lab | xss-lab-js-context | http://localhost:8026 | JavaScript context XSS - eksekusi dalam konteks JS |
| 8027 | JSON XSS Lab | xss-lab-json | http://localhost:8027 | JSON-based XSS - injeksi melalui JSON response |
| 8028 | Formaction XSS Lab | xss-lab-formaction | http://localhost:8028 | POST-based XSS via formaction attribute |
| 8029 | DOM Hash innerHTML XSS Lab | xss-lab-hash-innerhtml | http://localhost:8029 | DOM-based XSS via location.hash manipulation |
| 8030 | Search Query XSS Lab | xss-lab-search-query | http://localhost:8030 | Search query reflected XSS - injeksi di parameter search |
| 8031 | Attribute XSS Lab | xss-lab-attribute | http://localhost:8031 | HTML attribute context XSS - injeksi dalam atribut HTML |
| 8032 | JS String Context Lab | xss-lab-js-string-context | http://localhost:8032 | JavaScript string reflected XSS - refleksi dalam string JS |
| 8033 | Document Write Lab | xss-lab-document-write | http://localhost:8033 | document.write() XSS - injeksi via document.write |
| 8034 | innerHTML Lab | xss-lab-innerhtml | http://localhost:8034 | innerHTML injection XSS - injeksi langsung ke innerHTML |

---

## Standalone Labs (individual directories)

| Port | Lab Directory | Container Name | URL | Description |
|------|---------------|----------------|-----|-------------|
| 8035 | lab-xss-dom-location | lab-xss-dom-location-lab-xss-dom-location-1 | http://localhost:8035 | DOM Location XSS - manipulasi window.location object |
| 8036 | lab-xss-event-handler | lab-xss-event-handler-lab-xss-event-handler-1 | http://localhost:8036 | Event Handler XSS - injeksi via event handler (onclick, dll) |
| 8037 | lab-xss-js-string | lab-xss-js-string-lab-xss-js-string-1 | http://localhost:8037 | JS String XSS - variasi lain dari JavaScript string injection |
| 8038 | lab-xss-medium | lab-xss-medium-lab-xss-medium-1 | http://localhost:8038 | Medium XSS Lab - lab dengan tingkat kompleksitas medium |

---

## Quick Access Summary

### Reflected XSS Labs (input → immediate output)
- Port 8020: Reflected XSS
- Port 8030: Search Query
- Port 8031: Attribute Context
- Port 8032: JS String Context
- Port 8033: Document Write
- Port 8034: innerHTML

### Stored XSS Labs (payload → saved → executed)
- Port 8021: Stored XSS (blog comments)
- Port 8025: Stored XSS (href attribute)

### DOM-based XSS Labs (client-side manipulation)
- Port 8022: DOM XSS
- Port 8023: DOM innerHTML
- Port 8029: DOM Hash innerHTML
- Port 8035: DOM Location
- Port 8036: Event Handler

### JavaScript Context Labs
- Port 8024: JS String
- Port 8026: JS Context
- Port 8027: JSON
- Port 8037: JS String (standalone)

### Other Labs
- Port 8028: Formaction (POST-based)
- Port 8038: Medium difficulty

---

## Management Commands

### Check all running labs
```bash
docker ps --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"
```

### Stop all main labs
```bash
cd /root/documents/tools/lab-xss
docker-compose down
```

### Stop standalone labs
```bash
cd lab-xss-dom-location && docker-compose down
cd ../lab-xss-event-handler && docker-compose down
cd ../lab-xss-js-string && docker-compose down
cd ../lab-xss-medium && docker-compose down
```

### Restart specific lab
```bash
# Main labs
docker-compose restart reflected-xss-lab

# Standalone labs
cd lab-xss-dom-location
docker-compose restart
```

### View logs
```bash
# Main labs
docker-compose logs -f reflected-xss-lab

# Standalone labs
cd lab-xss-dom-location
docker-compose logs -f
```

---

## Testing Each Lab

### Quick test command
```bash
# Test all labs are responding
for port in {8020..8038}; do
  echo -n "Port $port: "
  curl -s -o /dev/null -w "%{http_code}" http://localhost:$port/ && echo " ✅"
done
```

### Example payloads for testing
```javascript
// Basic payload
<script>alert(1)</script>

// IMG tag
<img src=x onerror=alert(1)>

// SVG
<svg onload=alert(1)>

// For attribute contexts
" onmouseover=alert(1) x="

// For JS contexts
';alert(1);//

// For JSON
</script><script>alert(1)</script>
```

---

## Troubleshooting

### Port already in use
```bash
# Check what's using the port
sudo lsof -i :8020

# Or check with netstat
netstat -tulpn | grep 8020
```

### Container not starting
```bash
# Check logs
docker-compose logs <container-name>

# Check container status
docker ps -a
```

### Restart everything
```bash
# Stop all
docker-compose down
cd lab-xss-dom-location && docker-compose down
cd ../lab-xss-event-handler && docker-compose down
cd ../lab-xss-js-string && docker-compose down
cd ../lab-xss-medium && docker-compose down
cd ..

# Start all
cd lab-xss-dom-location && docker-compose up -d
cd ../lab-xss-event-handler && docker-compose up -d
cd ../lab-xss-js-string && docker-compose up -d
cd ../lab-xss-medium && docker-compose up -d
cd ..
docker-compose up -d
```

---

## Lab File Structure

```
/root/documents/tools/lab-xss/
├── docker-compose.yml          # Main labs (ports 8020-8034)
├── PORT_MAPPING.md             # This file
├── reflected_xss_lab/          # Port 8020
├── stored_xss_lab/             # Port 8021
├── dom_xss_lab/                # Port 8022
├── dom_innerhtml_xss_lab/      # Port 8023
├── js_string_xss_lab/          # Port 8024
├── stored_xss_href_lab/        # Port 8025
├── labs/                       # Shared files for some labs
├── formaction_xss_lab/         # Port 8028
├── hash_innerhtml_xss_lab/     # Port 8029
├── search_query_xss_lab/       # Port 8030
├── attribute_xss_lab/          # Port 8031
├── js_string_context_lab/      # Port 8032
├── document_write_lab/         # Port 8033
├── innerhtml_lab/              # Port 8034
├── lab-xss-dom-location/       # Port 8035 (standalone)
│   └── docker-compose.yml
├── lab-xss-event-handler/      # Port 8036 (standalone)
│   └── docker-compose.yml
├── lab-xss-js-string/          # Port 8037 (standalone)
│   └── docker-compose.yml
└── lab-xss-medium/             # Port 8038 (standalone)
    └── docker-compose.yml
```

---

*Last Updated: 2026-02-28*
*Total Labs: 19*
*Port Range: 8020-8038*
