# Separate XSS Labs Configuration

## Reflected XSS Lab
- **Port**: 8001
- **Access URL**: http://[SERVER_IP]:8001/reflected_xss_index.html
- **Main File**: search.php (with reflected XSS vulnerability)
- **Flag**: IDS{fdc13e38eb7c4e2bf9f157cab4a4304c}
- **Start Script**: ./start_reflected_xss.sh
- **Vulnerability**: Search functionality reflects user input without encoding

## Stored XSS Lab
- **Port**: 8002
- **Access URL**: http://[SERVER_IP]:8002/stored_xss_index.html
- **Main File**: blog_post.php (with stored XSS vulnerability)
- **Flag**: IDS{1c8a5c15517d898e873a11dd32a19fa4}
- **Start Script**: ./start_stored_xss.sh
- **Vulnerability**: Comment functionality stores and displays user input without encoding

## Setup Instructions

### For Reflected XSS Lab:
1. Run: `./start_reflected_xss.sh`
2. Access: `http://[SERVER_IP]:8001/reflected_xss_index.html`
3. Use search functionality to exploit XSS

### For Stored XSS Lab:
1. Run: `./start_stored_xss.sh`
2. Access: `http://[SERVER_IP]:8002/stored_xss_index.html`
3. Use comment functionality to exploit XSS

## Notes
- Both labs can run simultaneously on different ports
- Each lab has its own dedicated server instance
- The stored XSS lab uses comments.txt to persist comments between sessions
- Both vulnerabilities demonstrate HTML context injection without encoding