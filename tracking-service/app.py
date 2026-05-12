from flask import Flask, request, jsonify, send_file
import sqlite3
import os
import time

app = Flask(__name__)

DB_PATH = os.environ.get('DB_PATH', '/data/tracking.db')
ADMIN_TOKEN = os.environ.get('ADMIN_TOKEN', 'ids-admin-2024')

CATEGORIES = [
    {"key": "ssrf", "name": "Server-Side Request Forgery (SSRF)", "icon": "S",
     "color_start": "#e94560", "color_end": "#c23152"},
    {"key": "xss", "name": "Cross-Site Scripting (XSS)", "icon": "X",
     "color_start": "#f59e0b", "color_end": "#d97706"},
    {"key": "lfi", "name": "Local File Inclusion (LFI)", "icon": "L",
     "color_start": "#3b82f6", "color_end": "#2563eb"},
    {"key": "file-handling", "name": "File Handling", "icon": "F",
     "color_start": "#3b82f6", "color_end": "#2563eb"},
    {"key": "apisec", "name": "API Security & Broken Access Control", "icon": "A",
     "color_start": "#14b8a6", "color_end": "#f97316"},
    {"key": "sqli", "name": "SQL Injection (SQLi)", "icon": "S",
     "color_start": "#10b981", "color_end": "#059669"},
    {"key": "brute", "name": "Brute Force & Cracking", "icon": "B",
     "color_start": "#8b5cf6", "color_end": "#7c3aed"},
    {"key": "clientside", "name": "Client-Side Exploit", "icon": "C",
     "color_start": "#ec4899", "color_end": "#be185d"},
    {"key": "other", "name": "Other Labs", "icon": "O",
     "color_start": "#6b7280", "color_end": "#4b5563"},
]

LABS_SEED = [
    # SSRF (4)
    {"name": "SSRF Lab CTF", "category": "ssrf",
     "description": "6 level SSRF: basic, enumeration, hostname bypass, cloud metadata, blacklist bypass, open redirect.",
     "port": 8041, "difficulty": "Running", "badge_class": "badge-running",
     "css_class": "ssrf", "tracking_id": "ssrf-ctf", "order_num": 1},
    {"name": "Blind SSRF Lab", "category": "ssrf",
     "description": "3 level blind SSRF: exfiltrate flags from internal API via interceptor/OOB. Response never shown.",
     "port": 8042, "difficulty": "Running", "badge_class": "badge-running",
     "css_class": "ssrf", "tracking_id": "blind-ssrf", "order_num": 2},
    {"name": "Time-based SSRF Lab", "category": "ssrf",
     "description": "3 level time-based SSRF: detect internal services through timing analysis. No response visible.",
     "port": 8043, "difficulty": "Running", "badge_class": "badge-running",
     "css_class": "ssrf", "tracking_id": "timed-ssrf", "order_num": 3},
    {"name": "Simple SSRF", "category": "ssrf",
     "description": "Basic SSRF demo with Python Flask. No filter, no flag - pure vulnerability demonstration.",
     "port": 8001, "difficulty": "Running", "badge_class": "badge-running",
     "css_class": "ssrf", "tracking_id": "simple-ssrf", "order_num": 4},
    # XSS (20)
    {"name": "XSS - Reflected", "category": "xss",
     "description": "Classic reflected XSS vulnerability",
     "port": 8020, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "xss", "tracking_id": "xss-reflected", "order_num": 1},
    {"name": "XSS - Stored", "category": "xss",
     "description": "Persistent stored XSS attack",
     "port": 8021, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-stored", "order_num": 2},
    {"name": "XSS - DOM Based", "category": "xss",
     "description": "DOM-based cross-site scripting",
     "port": 8022, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-dom", "order_num": 3},
    {"name": "XSS - DOM innerHTML", "category": "xss",
     "description": "DOM XSS via innerHTML sink",
     "port": 8023, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-dom-innerhtml", "order_num": 4},
    {"name": "XSS - JS String", "category": "xss",
     "description": "XSS in JavaScript string context",
     "port": 8024, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "xss", "tracking_id": "xss-js-string", "order_num": 5},
    {"name": "XSS - Stored href", "category": "xss",
     "description": "Stored XSS via href attribute",
     "port": 8025, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-stored-href", "order_num": 6},
    {"name": "XSS - JS Context", "category": "xss",
     "description": "XSS within JavaScript context",
     "port": 8026, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-js-context", "order_num": 7},
    {"name": "XSS - JSON", "category": "xss",
     "description": "XSS through JSON injection",
     "port": 8027, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-json", "order_num": 8},
    {"name": "XSS - Form Action", "category": "xss",
     "description": "XSS via form action injection",
     "port": 8028, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-formaction", "order_num": 9},
    {"name": "XSS - Hash innerHTML", "category": "xss",
     "description": "XSS via location hash + innerHTML",
     "port": 8029, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-hash-innerhtml", "order_num": 10},
    {"name": "XSS - Search Query", "category": "xss",
     "description": "XSS in search query parameter",
     "port": 8030, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "xss", "tracking_id": "xss-search-query", "order_num": 11},
    {"name": "XSS - Attribute", "category": "xss",
     "description": "XSS in HTML attribute context",
     "port": 8031, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "xss", "tracking_id": "xss-attribute", "order_num": 12},
    {"name": "XSS - JS String Context", "category": "xss",
     "description": "XSS breaking out of JS string",
     "port": 8032, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-js-string-context", "order_num": 13},
    {"name": "XSS - document.write", "category": "xss",
     "description": "XSS via document.write sink",
     "port": 8033, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "xss", "tracking_id": "xss-document-write", "order_num": 14},
    {"name": "XSS - innerHTML", "category": "xss",
     "description": "XSS via innerHTML assignment",
     "port": 8034, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "xss", "tracking_id": "xss-innerhtml", "order_num": 15},
    {"name": "XSS - DOM Location", "category": "xss",
     "description": "DOM XSS via location source",
     "port": 8035, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-dom-location", "order_num": 16},
    {"name": "XSS - Event Handler", "category": "xss",
     "description": "XSS via event handler injection",
     "port": 8036, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-event-handler", "order_num": 17},
    {"name": "XSS - JS String (Medium)", "category": "xss",
     "description": "JS string XSS with filter bypass",
     "port": 8037, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-js-string-adv", "order_num": 18},
    {"name": "XSS - Medium", "category": "xss",
     "description": "Medium difficulty XSS challenge",
     "port": 8038, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "xss", "tracking_id": "xss-medium", "order_num": 19},
    {"name": "Advanced XSS Lab", "category": "xss",
     "description": "8 advanced challenges: WAF bypass, CSP evasion, mXSS, blind XSS, polyglot XSS and more.",
     "port": 8060, "difficulty": "Advanced", "badge_class": "badge-hard",
     "css_class": "xss", "tracking_id": "advanced-xss", "order_num": 20},
    # LFI (6)
    {"name": "LFI Lab", "category": "lfi",
     "description": "Local File Inclusion vulnerability with multiple attack vectors.",
     "port": 8039, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "lfi", "tracking_id": "lfi", "order_num": 1},
    {"name": "LFI Lab - Easy", "category": "lfi",
     "description": "Beginner-friendly Local File Inclusion challenge.",
     "port": 8040, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "lfi", "tracking_id": "lfi-easy", "order_num": 2},
    {"name": "LFI Cookie-based", "category": "lfi",
     "description": "3 levels: exploit LFI through cookie manipulation, filter bypass with ....// and double encoding.",
     "port": 8044, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "lfi", "tracking_id": "lfi-cookie", "order_num": 3},
    {"name": "LFI Null Byte", "category": "lfi",
     "description": "3 levels: null byte injection, double encoding null byte, and path truncation bypass.",
     "port": 8045, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "lfi", "tracking_id": "lfi-nullbyte", "order_num": 4},
    {"name": "LFI Wrapper Challenge", "category": "lfi",
     "description": "4 levels: php://filter, php://input, data://, and phar:// wrapper exploitation.",
     "port": 8046, "difficulty": "Hard", "badge_class": "badge-hard",
     "css_class": "lfi", "tracking_id": "lfi-wrappers", "order_num": 5},
    {"name": "LFI to RCE Chain", "category": "lfi",
     "description": "5 levels: /proc/self/environ, log poisoning, session poisoning, /proc/self/fd/, temp file race.",
     "port": 8047, "difficulty": "Expert", "badge_class": "badge-expert",
     "css_class": "lfi", "tracking_id": "lfi-rce", "order_num": 6},
    # File Handling (3)
    {"name": "File Handling & LFI/RFI Lab", "category": "file-handling",
     "description": "5 progressive challenges: Path Traversal, Filter Bypass, PHP Wrappers, RFI, Log Poisoning.",
     "port": 8061, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "lfi", "tracking_id": "file-handling", "order_num": 1},
    {"name": "Path Traversal Lab", "category": "file-handling",
     "description": "4 levels: basic traversal, double encoding, unicode normalization bypass, upload + traversal write.",
     "port": 8064, "difficulty": "Easy/Medium", "badge_class": "badge-easy",
     "css_class": "lfi", "tracking_id": "path-traversal", "order_num": 2},
    {"name": "Upload Bypass Lab", "category": "file-handling",
     "description": "5 levels: extension bypass, MIME spoofing, magic bytes, .htaccess override, double extension.",
     "port": 8065, "difficulty": "Medium/Hard", "badge_class": "badge-hard",
     "css_class": "lfi", "tracking_id": "upload-bypass", "order_num": 3},
    # API Security (2)
    {"name": "API Security Lab", "category": "apisec",
     "description": "4 levels: broken authentication, excessive data exposure, mass assignment, JWT token manipulation.",
     "port": 8062, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "apisec", "tracking_id": "api-security", "order_num": 1},
    {"name": "Broken Access Control Lab", "category": "apisec",
     "description": "4 levels: IDOR, horizontal & vertical privilege escalation, forceful browsing.",
     "port": 8063, "difficulty": "Advanced", "badge_class": "badge-hard",
     "css_class": "apisec", "tracking_id": "broken-access", "order_num": 2},
    # SQLi (6)
    {"name": "SQLi - Login Bypass", "category": "sqli",
     "description": "SQL injection to bypass authentication",
     "port": 5001, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "sqli", "tracking_id": "sqli-login-bypass", "order_num": 1},
    {"name": "SQLi - Other Endpoints", "category": "sqli",
     "description": "SQL injection on non-obvious endpoints",
     "port": 5002, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "sqli", "tracking_id": "sqli-other-endpoints", "order_num": 2},
    {"name": "SQLi - Hidden Data", "category": "sqli",
     "description": "Extracting hidden database contents",
     "port": 5003, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "sqli", "tracking_id": "sqli-hidden-data", "order_num": 3},
    {"name": "Oracle Version Detection", "category": "sqli",
     "description": "Database fingerprinting and version detection",
     "port": 5005, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "sqli", "tracking_id": "sqli-oracle-version", "order_num": 4},
    {"name": "SQLi - DB Version", "category": "sqli",
     "description": "SQL injection to determine database version",
     "port": 5006, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "sqli", "tracking_id": "sqli-db-version", "order_num": 5},
    {"name": "SQL Injection Lab", "category": "sqli",
     "description": "Comprehensive SQL injection training",
     "port": 6003, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "sqli", "tracking_id": "sqli-lab", "order_num": 6},
    # Brute Force (3)
    {"name": "Brute Force - Password Challenge", "category": "brute",
     "description": "Password brute force attack challenge",
     "port": 6001, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "brute", "tracking_id": "brute-password", "order_num": 1},
    {"name": "Hashcat & John Lab", "category": "brute",
     "description": "Hash cracking with Hashcat and John the Ripper",
     "port": 6002, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "brute", "tracking_id": "brute-hashcat", "order_num": 2},
    {"name": "Brute Force - Advanced", "category": "brute",
     "description": "Advanced brute force techniques and tools",
     "port": 6004, "difficulty": "Hard", "badge_class": "badge-hard",
     "css_class": "brute", "tracking_id": "brute-advanced", "order_num": 3},
    # Client-Side (7)
    {"name": "Client-Side Restriction Bypass", "category": "clientside",
     "description": "Bypass client-side input validation to inject malicious sleep time values.",
     "port": 8050, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "clientside", "tracking_id": "client-restriction", "order_num": 1},
    {"name": "Client-Side Template Injection (CSTI)", "category": "clientside",
     "description": "Exploit client-side template injection via unsanitized user input rendering.",
     "port": 8051, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "clientside", "tracking_id": "client-csti", "order_num": 2},
    {"name": "CSRF - Basic", "category": "clientside",
     "description": "Cross-Site Request Forgery attack with no CSRF protection on color preference update.",
     "port": 8052, "difficulty": "Easy", "badge_class": "badge-easy",
     "css_class": "clientside", "tracking_id": "csrf-basic", "order_num": 3},
    {"name": "CSRF - SameSite Cookie", "category": "clientside",
     "description": "CSRF with different SameSite cookie settings: strict, lax, and none.",
     "port": 8053, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "clientside", "tracking_id": "csrf-samesite", "order_num": 4},
    {"name": "CSRF - Weak Token", "category": "clientside",
     "description": "Exploit predictable time-based CSRF token to forge requests.",
     "port": 8054, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "clientside", "tracking_id": "csrf-weak", "order_num": 5},
    {"name": "Session Hijacking via XSS", "category": "clientside",
     "description": "Steal session cookies through XSS with httpOnly disabled.",
     "port": 8055, "difficulty": "Hard", "badge_class": "badge-hard",
     "css_class": "clientside", "tracking_id": "session-hijack", "order_num": 6},
    {"name": "Untrusted Sources (XSSI)", "category": "clientside",
     "description": "Cross-Site Script Inclusion attack via untrusted JavaScript sources.",
     "port": 8056, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "clientside", "tracking_id": "xssi", "order_num": 7},
    # Other (1)
    {"name": "VulnLab", "category": "other",
     "description": "Multi-vulnerability training environment",
     "port": 2222, "difficulty": "Medium", "badge_class": "badge-medium",
     "css_class": "other", "tracking_id": "vulnlab", "order_num": 1},
]


def get_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn


def init_db():
    conn = get_db()
    conn.execute('''CREATE TABLE IF NOT EXISTS hits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lab TEXT NOT NULL,
        ip TEXT DEFAULT '',
        timestamp REAL NOT NULL
    )''')
    conn.execute('''CREATE TABLE IF NOT EXISTS flag_captures (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lab TEXT NOT NULL,
        flag TEXT NOT NULL,
        ip TEXT DEFAULT '',
        timestamp REAL NOT NULL
    )''')
    conn.execute('''CREATE TABLE IF NOT EXISTS labs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        description TEXT DEFAULT '',
        port INTEGER NOT NULL UNIQUE,
        difficulty TEXT DEFAULT '',
        badge_class TEXT DEFAULT '',
        css_class TEXT DEFAULT '',
        tracking_id TEXT DEFAULT '',
        published INTEGER DEFAULT 1,
        order_num INTEGER DEFAULT 0
    )''')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_hits_lab ON hits(lab)')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_flags_lab ON flag_captures(lab)')

    # Seed labs if table is empty
    count = conn.execute('SELECT COUNT(*) as c FROM labs').fetchone()['c']
    if count == 0:
        for lab in LABS_SEED:
            conn.execute(
                '''INSERT INTO labs (name, category, description, port, difficulty,
                   badge_class, css_class, tracking_id, published, order_num)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)''',
                (lab['name'], lab['category'], lab['description'], lab['port'],
                 lab['difficulty'], lab['badge_class'], lab['css_class'],
                 lab['tracking_id'], lab['order_num'])
            )
        conn.commit()

    conn.commit()
    conn.close()


init_db()


def check_admin_token():
    token = request.headers.get('X-Admin-Token', '')
    if token != ADMIN_TOKEN:
        return jsonify({'error': 'unauthorized'}), 401
    return None


@app.route('/api/hit', methods=['GET', 'POST'])
def record_hit():
    lab = request.values.get('lab', '')
    ip = request.values.get('ip', '')
    if not lab:
        return jsonify({'error': 'lab parameter required'}), 400

    conn = get_db()
    conn.execute('INSERT INTO hits (lab, ip, timestamp) VALUES (?, ?, ?)',
                 (lab, ip, time.time()))
    conn.commit()
    count = conn.execute('SELECT COUNT(*) as c FROM hits WHERE lab = ?', (lab,)).fetchone()['c']
    conn.close()
    return jsonify({'status': 'ok', 'lab': lab, 'hits': count})


@app.route('/api/flag', methods=['GET', 'POST'])
def record_flag():
    lab = request.values.get('lab', '')
    flag = request.values.get('flag', '')
    ip = request.values.get('ip', '')
    if not lab or not flag:
        return jsonify({'error': 'lab and flag parameters required'}), 400

    conn = get_db()
    conn.execute('INSERT INTO flag_captures (lab, flag, ip, timestamp) VALUES (?, ?, ?, ?)',
                 (lab, flag, ip, time.time()))
    conn.commit()
    count = conn.execute('SELECT COUNT(*) as c FROM flag_captures WHERE lab = ?', (lab,)).fetchone()['c']
    conn.close()
    return jsonify({'status': 'ok', 'lab': lab, 'captures': count})


@app.route('/api/stats', methods=['GET'])
def get_stats():
    conn = get_db()

    hits = conn.execute(
        'SELECT lab, COUNT(*) as count FROM hits GROUP BY lab'
    ).fetchall()

    flags = conn.execute(
        'SELECT lab, COUNT(*) as count FROM flag_captures GROUP BY lab'
    ).fetchall()

    total_hits = conn.execute('SELECT COUNT(*) as c FROM hits').fetchone()['c']
    total_flags = conn.execute('SELECT COUNT(*) as c FROM flag_captures').fetchone()['c']

    recent_flags = conn.execute(
        'SELECT lab, flag, ip, timestamp FROM flag_captures ORDER BY timestamp DESC LIMIT 50'
    ).fetchall()

    conn.close()

    return jsonify({
        'total_hits': total_hits,
        'total_flag_captures': total_flags,
        'hits_by_lab': {row['lab']: row['count'] for row in hits},
        'flags_by_lab': {row['lab']: row['count'] for row in flags},
        'recent_flags': [{'lab': r['lab'], 'flag': r['flag'], 'ip': r['ip'],
                          'time': r['timestamp']} for r in recent_flags]
    })


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok'})


# --- Lab Management Endpoints ---

@app.route('/api/labs', methods=['GET'])
def get_published_labs():
    conn = get_db()
    rows = conn.execute(
        'SELECT * FROM labs WHERE published = 1 ORDER BY category, order_num'
    ).fetchall()
    conn.close()

    cat_map = {}
    for row in rows:
        cat_key = row['category']
        if cat_key not in cat_map:
            cat_map[cat_key] = []
        cat_map[cat_key].append({
            'id': row['id'],
            'name': row['name'],
            'description': row['description'],
            'port': row['port'],
            'difficulty': row['difficulty'],
            'badge_class': row['badge_class'],
            'css_class': row['css_class'],
            'tracking_id': row['tracking_id'],
        })

    categories = []
    for cat in CATEGORIES:
        if cat['key'] in cat_map:
            categories.append({
                'key': cat['key'],
                'name': cat['name'],
                'icon': cat['icon'],
                'color_start': cat['color_start'],
                'color_end': cat['color_end'],
                'labs': cat_map[cat['key']],
                'count': len(cat_map[cat['key']]),
            })

    total = sum(len(c['labs']) for c in categories)
    return jsonify({
        'categories': categories,
        'total_labs': total,
        'total_categories': len(categories),
    })


@app.route('/api/labs/all', methods=['GET'])
def get_all_labs():
    auth_err = check_admin_token()
    if auth_err:
        return auth_err

    conn = get_db()
    rows = conn.execute(
        'SELECT * FROM labs ORDER BY category, order_num'
    ).fetchall()
    conn.close()

    cat_map = {}
    for row in rows:
        cat_key = row['category']
        if cat_key not in cat_map:
            cat_map[cat_key] = []
        cat_map[cat_key].append({
            'id': row['id'],
            'name': row['name'],
            'description': row['description'],
            'port': row['port'],
            'difficulty': row['difficulty'],
            'badge_class': row['badge_class'],
            'css_class': row['css_class'],
            'tracking_id': row['tracking_id'],
            'published': bool(row['published']),
        })

    categories = []
    for cat in CATEGORIES:
        if cat['key'] in cat_map:
            categories.append({
                'key': cat['key'],
                'name': cat['name'],
                'icon': cat['icon'],
                'color_start': cat['color_start'],
                'color_end': cat['color_end'],
                'labs': cat_map[cat['key']],
                'count': len(cat_map[cat_key]),
            })

    all_labs = [row for row in rows]
    total = len(all_labs)
    published = sum(1 for r in all_labs if r['published'])
    draft = total - published

    return jsonify({
        'categories': categories,
        'total': total,
        'published': published,
        'draft': draft,
    })


@app.route('/api/labs/toggle', methods=['POST'])
def toggle_lab():
    auth_err = check_admin_token()
    if auth_err:
        return auth_err

    data = request.get_json()
    if not data or 'port' not in data:
        return jsonify({'error': 'port required'}), 400

    conn = get_db()
    row = conn.execute('SELECT * FROM labs WHERE port = ?', (data['port'],)).fetchone()
    if not row:
        conn.close()
        return jsonify({'error': 'lab not found'}), 404

    new_status = 0 if row['published'] else 1
    conn.execute('UPDATE labs SET published = ? WHERE port = ?', (new_status, data['port']))
    conn.commit()
    conn.close()

    return jsonify({
        'status': 'ok',
        'port': data['port'],
        'published': bool(new_status),
    })


@app.route('/admin')
def admin_page():
    return send_file('admin.html')


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8080)
