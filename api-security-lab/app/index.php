<?php
error_reporting(0);

// ============ TRACKING ============
function trackHit($labId) {
    @file_get_contents("http://tracking-service:8080/api/hit?" . http_build_query([
        'lab' => $labId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]));
}
function trackFlag($labId, $flag) {
    @file_get_contents("http://tracking-service:8080/api/flag?" . http_build_query([
        'lab' => $labId,
        'flag' => $flag,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]));
}
trackHit('api-security');

// ============ CONFIGURATION ============
$flags = [
    1 => file_exists('/var/flags/flag1.txt') ? trim(file_get_contents('/var/flags/flag1.txt')) : 'IDS{flag1_placeholder}',
    2 => file_exists('/var/flags/flag2.txt') ? trim(file_get_contents('/var/flags/flag2.txt')) : 'IDS{flag2_placeholder}',
    3 => file_exists('/var/flags/flag3.txt') ? trim(file_get_contents('/var/flags/flag3.txt')) : 'IDS{flag3_placeholder}',
    4 => file_exists('/var/flags/flag4.txt') ? trim(file_get_contents('/var/flags/flag4.txt')) : 'IDS{flag4_placeholder}',
];

$levels = [
    1 => ['name' => 'Broken Authentication', 'difficulty' => 'Easy', 'color' => '#10b981'],
    2 => ['name' => 'Excessive Data Exposure', 'difficulty' => 'Easy', 'color' => '#10b981'],
    3 => ['name' => 'Mass Assignment', 'difficulty' => 'Medium', 'color' => '#f59e0b'],
    4 => ['name' => 'JWT Token Manipulation', 'difficulty' => 'Medium', 'color' => '#f59e0b'],
];

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 4) $level = 1;

// ============ USERS DATABASE ============
$users = [
    1 => ['id' => 1, 'username' => 'alice', 'password' => 'alice123', 'role' => 'user', 'email' => 'alice@ids.local'],
    2 => ['id' => 2, 'username' => 'bob', 'password' => 'bob456', 'role' => 'user', 'email' => 'bob@ids.local'],
    3 => ['id' => 3, 'username' => 'admin', 'password' => 'admin789', 'role' => 'admin', 'email' => 'admin@ids.local'],
];

// ============ JWT HELPER (Simple base64) ============
function base64url_encode($data) {
    return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
}
function base64url_decode($data) {
    $decoded = base64_decode(strtr($data, '-_', '+/'));
    return json_decode($decoded, true);
}
function createToken($payload) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $h = base64url_encode($header);
    $p = base64url_encode($payload);
    $signature = base64url_encode(['valid' => true]);
    return "$h.$p.$signature";
}
function parseToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    $header = base64url_decode($parts[0]);
    $payload = base64url_decode($parts[1]);
    if (!$payload) return null;
    return ['header' => $header, 'payload' => $payload];
}

function getAuthToken() {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// ============ API ROUTER ============
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove trailing slash except root
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// API routes
if (strpos($uri, '/api/') === 0) {
    // Route: POST /api/login
    if ($uri === '/api/login' && $method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['username']) || !isset($input['password'])) {
            jsonResponse(['error' => 'Username and password required'], 400);
        }
        foreach ($users as $u) {
            if ($u['username'] === $input['username'] && $u['password'] === $input['password']) {
                $token = createToken(['user_id' => $u['id'], 'username' => $u['username'], 'role' => $u['role']]);
                jsonResponse(['token' => $token, 'user' => ['id' => $u['id'], 'username' => $u['username'], 'role' => $u['role']]]);
            }
        }
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }

    // Route: GET /api/me
    if ($uri === '/api/me' && $method === 'GET') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'No token provided'], 401);
        $parsed = parseToken($token);
        if (!$parsed) jsonResponse(['error' => 'Invalid token'], 401);
        jsonResponse(['user' => $parsed['payload']]);
    }

    // ============ LEVEL 1: Broken Authentication ============
    // GET /api/admin/users - No auth check (intentionally vulnerable)
    if ($uri === '/api/admin/users' && $method === 'GET') {
        // VULNERABILITY: No authentication check at all!
        // Returns all users with passwords
        $exposed = [];
        foreach ($users as $u) {
            $exposed[] = [
                'id' => $u['id'],
                'username' => $u['username'],
                'password' => $u['password'],
                'role' => $u['role'],
                'email' => $u['email'],
                'secret' => $flags[1]
            ];
        }
        trackFlag('api-security', $flags[1]);
        jsonResponse(['users' => $exposed]);
    }

    // ============ LEVEL 2: Excessive Data Exposure ============
    // GET /api/profile - Returns sensitive data (password hash)
    if ($uri === '/api/profile' && $method === 'GET') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'Login required. POST /api/login first.'], 401);
        $parsed = parseToken($token);
        if (!$parsed) jsonResponse(['error' => 'Invalid token'], 401);

        $userId = $parsed['payload']['user_id'];
        $user = $users[$userId];

        // VULNERABILITY: Returns password hash and other sensitive info
        trackFlag('api-security', $flags[2]);
        jsonResponse([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'password_hash' => md5($user['password']),
            'internal_notes' => 'User profile data - contains sensitive information',
            'debug_info' => [
                'db_connection' => 'mysql://root:admin123@localhost/ids_labs',
                'api_key' => $flags[2],
                'server_version' => '2.1.0-dev'
            ]
        ]);
    }

    // ============ LEVEL 3: Mass Assignment ============
    // PUT /api/profile - Accepts extra fields like role
    if ($uri === '/api/profile' && $method === 'PUT') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'Login required'], 401);
        $parsed = parseToken($token);
        if (!$parsed) jsonResponse(['error' => 'Invalid token'], 401);

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Invalid JSON'], 400);

        $userId = $parsed['payload']['user_id'];
        $user = $users[$userId];

        // VULNERABILITY: Mass assignment - accepts any field including 'role'
        $updated = array_merge($user, $input);

        // Check if user escalated to admin
        if (isset($input['role']) && $input['role'] === 'admin') {
            trackFlag('api-security', $flags[3]);
            jsonResponse([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $updated['id'],
                    'username' => $updated['username'],
                    'role' => $updated['role'],
                    'email' => $updated['email']
                ],
                'admin_flag' => $flags[3]
            ]);
        }

        jsonResponse([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $updated['id'],
                'username' => $updated['username'],
                'role' => $updated['role'],
                'email' => $updated['email']
            ]
        ]);
    }

    // ============ LEVEL 4: JWT Token Manipulation (alg:none) ============
    // GET /api/admin/flag - Check JWT, but accepts alg:none
    if ($uri === '/api/admin/flag' && $method === 'GET') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'Login required'], 401);

        $parsed = parseToken($token);
        if (!$parsed) jsonResponse(['error' => 'Invalid token'], 401);

        // VULNERABILITY: Accepts 'none' algorithm - if role is admin in payload, grant access
        if (isset($parsed['payload']['role']) && $parsed['payload']['role'] === 'admin') {
            trackFlag('api-security', $flags[4]);
            jsonResponse([
                'message' => 'Welcome admin!',
                'flag' => $flags[4],
                'admin_data' => [
                    'total_users' => 3,
                    'system_status' => 'operational'
                ]
            ]);
        }

        jsonResponse(['error' => 'Access denied. Admin role required.'], 403);
    }

    // Catch-all for unknown API routes
    jsonResponse(['error' => 'Endpoint not found', 'level' => $level], 404);
}

// ============ FRONTEND UI ============
$currentLevel = $levels[$level];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Security Lab - IDS Cybersecurity</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #0a0a1a;
            color: #e0e0e0;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            padding: 40px 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .header h1 {
            font-size: 2.2em;
            font-weight: 800;
            background: linear-gradient(135deg, #14b8a6, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }
        .header p {
            color: #9ca3af;
            font-size: 1em;
        }

        /* Level Tabs */
        .level-tabs {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 20px;
            background: rgba(15,15,30,0.8);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            flex-wrap: wrap;
        }
        .level-tab {
            padding: 8px 20px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(17,17,35,0.9);
            color: #9ca3af;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
        }
        .level-tab:hover {
            border-color: rgba(20,184,166,0.5);
            color: #e0e0e0;
        }
        .level-tab.active {
            background: rgba(20,184,166,0.15);
            border-color: #14b8a6;
            color: #14b8a6;
        }
        .level-tab .diff {
            font-size: 0.75em;
            opacity: 0.7;
        }

        /* Main Content */
        .main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .level-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .level-badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-easy { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-medium { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-hard { background: rgba(239,68,68,0.15); color: #ef4444; }

        .level-title {
            font-size: 1.5em;
            font-weight: 700;
            color: #f3f4f6;
        }

        /* Two Column Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 900px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* Clue Box */
        .clue-box {
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .clue-box h3 {
            color: #f59e0b;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .clue-box p {
            color: #d1d5db;
            font-size: 0.85em;
            line-height: 1.6;
        }
        .clue-box code {
            background: rgba(0,0,0,0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85em;
            color: #f59e0b;
        }

        /* API Playground */
        .panel {
            background: rgba(17,17,35,0.9);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            overflow: hidden;
        }
        .panel-header {
            padding: 12px 16px;
            background: rgba(0,0,0,0.3);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-size: 0.85em;
            font-weight: 600;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .panel-body {
            padding: 16px;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            font-size: 0.8em;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .form-row {
            display: flex;
            gap: 8px;
        }
        select, input, textarea {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px;
            padding: 8px 12px;
            color: #e0e0e0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85em;
            width: 100%;
            outline: none;
        }
        select:focus, input:focus, textarea:focus {
            border-color: #14b8a6;
        }
        select { cursor: pointer; }
        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-secondary {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #9ca3af;
        }
        .btn-secondary:hover { border-color: rgba(255,255,255,0.3); color: #e0e0e0; }

        /* Response Viewer */
        .response-status {
            padding: 8px 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8em;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .status-2xx { color: #10b981; }
        .status-4xx { color: #ef4444; }
        .status-5xx { color: #ef4444; }
        .response-body {
            padding: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8em;
            color: #d1d5db;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 400px;
            overflow-y: auto;
            background: rgba(0,0,0,0.2);
        }

        /* Hint Section */
        .hint-section {
            margin-top: 20px;
        }
        .hint-toggle {
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.2);
            color: #818cf8;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85em;
            width: 100%;
            text-align: left;
            transition: all 0.3s;
        }
        .hint-toggle:hover { background: rgba(99,102,241,0.15); }
        .hint-content {
            display: none;
            background: rgba(99,102,241,0.05);
            border: 1px solid rgba(99,102,241,0.1);
            border-radius: 0 0 8px 8px;
            padding: 14px;
            font-size: 0.85em;
            line-height: 1.6;
            color: #c4b5fd;
        }
        .hint-content.show { display: block; }
        .hint-content code {
            background: rgba(0,0,0,0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            color: #a78bfa;
        }

        /* Credentials Box */
        .creds-box {
            background: rgba(59,130,246,0.08);
            border: 1px solid rgba(59,130,246,0.2);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 16px;
        }
        .creds-box h4 {
            color: #60a5fa;
            font-size: 0.85em;
            margin-bottom: 8px;
        }
        .creds-box table {
            width: 100%;
            font-size: 0.8em;
        }
        .creds-box td {
            padding: 3px 8px;
            font-family: 'JetBrains Mono', monospace;
        }
        .creds-box td:first-child { color: #6b7280; }

        /* Flag Display */
        .flag-display {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
            display: none;
        }
        .flag-display.show { display: block; }
        .flag-display .flag-label {
            font-size: 0.8em;
            color: #10b981;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .flag-display .flag-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85em;
            color: #34d399;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>API Security Lab</h1>
        <p>Exploit common API vulnerabilities to capture flags</p>
    </div>

    <div class="level-tabs">
        <?php for ($i = 1; $i <= 4; $i++): ?>
        <a href="?level=<?php echo $i; ?>" class="level-tab <?php echo $level == $i ? 'active' : ''; ?>">
            Level <?php echo $i; ?>: <?php echo $levels[$i]['name']; ?>
            <span class="diff">(<?php echo $levels[$i]['difficulty']; ?>)</span>
        </a>
        <?php endfor; ?>
    </div>

    <div class="main">
        <div class="level-info">
            <span class="level-badge <?php echo $currentLevel['difficulty'] === 'Easy' ? 'badge-easy' : ($currentLevel['difficulty'] === 'Medium' ? 'badge-medium' : 'badge-hard'); ?>">
                <?php echo $currentLevel['difficulty']; ?>
            </span>
            <span class="level-title">Level <?php echo $level; ?>: <?php echo $currentLevel['name']; ?></span>
        </div>

        <?php if ($level === 1): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Find the flag by accessing the admin users endpoint. No authentication is required to access this API endpoint.<br>
            Try: <code>GET /api/admin/users</code></p>
        </div>
        <?php elseif ($level === 2): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Login first, then access your profile. The API returns more data than it should. Look for sensitive information in the response.<br>
            Login: <code>POST /api/login {"username":"alice","password":"alice123"}</code><br>
            Then: <code>GET /api/profile</code> with the token.</p>
        </div>
        <?php elseif ($level === 3): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Login as a regular user, then update your profile. The API accepts any field you send - try adding extra fields to escalate privileges.<br>
            Login: <code>POST /api/login {"username":"alice","password":"alice123"}</code><br>
            Then: <code>PUT /api/profile</code> with extra fields in the body.</p>
        </div>
        <?php elseif ($level === 4): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Login as a regular user, get the JWT token, then manipulate it. The server uses a weak signing algorithm.<br>
            Login: <code>POST /api/login {"username":"alice","password":"alice123"}</code><br>
            Decode the token, modify the payload to change your role to "admin", and access: <code>GET /api/admin/flag</code></p>
        </div>
        <?php endif; ?>

        <!-- Credentials -->
        <div class="creds-box">
            <h4>Test Credentials</h4>
            <table>
                <tr><td>alice</td><td>: alice123</td><td>(user)</td></tr>
                <tr><td>bob</td><td>: bob456</td><td>(user)</td></tr>
                <tr><td>admin</td><td>: admin789</td><td>(admin)</td></tr>
            </table>
        </div>

        <div class="content-grid">
            <!-- Request Panel -->
            <div class="panel">
                <div class="panel-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#14b8a6" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    API Request
                </div>
                <div class="panel-body">
                    <div class="form-row">
                        <div style="width: 110px;">
                            <div class="form-group">
                                <label>Method</label>
                                <select id="reqMethod">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>
                        </div>
                        <div style="flex:1;">
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" id="reqUrl" value="/api/" placeholder="/api/endpoint">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Authorization Header (Bearer Token)</label>
                        <input type="text" id="reqToken" placeholder="Paste your JWT token here">
                    </div>
                    <div class="form-group">
                        <label>Request Body (JSON)</label>
                        <textarea id="reqBody" placeholder='{"key": "value"}'></textarea>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn btn-primary" onclick="sendRequest()">Send Request</button>
                        <button class="btn btn-secondary" onclick="quickLogin()">Quick Login (alice)</button>
                    </div>
                </div>
            </div>

            <!-- Response Panel -->
            <div class="panel">
                <div class="panel-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    API Response
                </div>
                <div class="response-status" id="responseStatus">Waiting for request...</div>
                <div class="response-body" id="responseBody">// Response will appear here</div>
            </div>
        </div>

        <!-- Flag Display -->
        <div class="flag-display" id="flagDisplay">
            <div class="flag-label">FLAG CAPTURED!</div>
            <div class="flag-value" id="flagValue"></div>
        </div>

        <!-- Hint Section -->
        <div class="hint-section">
            <button class="hint-toggle" onclick="toggleHint()">
                &#9654; Show Hint
            </button>
            <div class="hint-content" id="hintContent">
                <?php if ($level === 1): ?>
                    <strong>Hint:</strong> The admin users endpoint has no authentication check. Simply send a GET request to <code>/api/admin/users</code> without any token. The flag is in the response under the <code>secret</code> field.
                <?php elseif ($level === 2): ?>
                    <strong>Hint:</strong> After logging in, call <code>GET /api/profile</code> with your token. Look at the <code>debug_info</code> field - it contains an API key which is the flag. This is excessive data exposure!
                <?php elseif ($level === 3): ?>
                    <strong>Hint:</strong> Send a PUT request to <code>/api/profile</code> with your token. In the JSON body, add <code>{"role": "admin"}</code> along with other fields. The API accepts any field due to mass assignment vulnerability.
                <?php elseif ($level === 4): ?>
                    <strong>Hint:</strong> The JWT uses a simple base64 encoding (not real JWT). Decode the token parts using base64, modify the payload to set <code>"role": "admin"</code>, re-encode it, and use the forged token to access <code>GET /api/admin/flag</code>.
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let currentToken = '';
        const BASE = window.location.pathname.replace(/\/$/, '').replace(/\/level\/?\d*$/, '') || '';
        function proxyUrl(url) {
            if (url.startsWith('/') && BASE) return BASE + url;
            return url;
        }

        function toggleHint() {
            const content = document.getElementById('hintContent');
            const btn = document.querySelector('.hint-toggle');
            content.classList.toggle('show');
            btn.innerHTML = content.classList.contains('show') ? '&#9660; Hide Hint' : '&#9654; Show Hint';
        }

        function checkForFlag(text) {
            const match = text.match(/IDS\{[^}]+\}/);
            if (match) {
                document.getElementById('flagDisplay').classList.add('show');
                document.getElementById('flagValue').textContent = match[0];
            }
        }

        function quickLogin() {
            const method = 'POST';
            const url = proxyUrl('/api/login');
            const body = JSON.stringify({username: 'alice', password: 'alice123'});

            fetch(url, {
                method: method,
                headers: {'Content-Type': 'application/json'},
                body: body
            })
            .then(r => r.text())
            .then(text => {
                try {
                    const json = JSON.parse(text);
                    document.getElementById('responseBody').textContent = JSON.stringify(json, null, 2);
                    document.getElementById('responseStatus').innerHTML = '<span class="status-2xx">200 OK</span>';

                    if (json.token) {
                        currentToken = json.token;
                        document.getElementById('reqToken').value = json.token;
                    }
                    checkForFlag(text);
                } catch(e) {
                    document.getElementById('responseBody').textContent = text;
                }
            })
            .catch(err => {
                document.getElementById('responseBody').textContent = 'Error: ' + err.message;
                document.getElementById('responseStatus').innerHTML = '<span class="status-5xx">Error</span>';
            });
        }

        function sendRequest() {
            const method = document.getElementById('reqMethod').value;
            let url = proxyUrl(document.getElementById('reqUrl').value);
            const token = document.getElementById('reqToken').value;
            const body = document.getElementById('reqBody').value;

            const headers = {'Content-Type': 'application/json'};
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            const options = { method: method, headers: headers };
            if (body && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                options.body = body;
            }

            document.getElementById('responseStatus').innerHTML = 'Sending...';
            document.getElementById('responseBody').textContent = '// Loading...';

            fetch(url, options)
            .then(r => {
                const statusClass = r.status < 300 ? 'status-2xx' : (r.status < 500 ? 'status-4xx' : 'status-5xx');
                document.getElementById('responseStatus').innerHTML = '<span class="' + statusClass + '">' + r.status + ' ' + r.statusText + '</span>';
                return r.text();
            })
            .then(text => {
                try {
                    const json = JSON.parse(text);
                    const formatted = JSON.stringify(json, null, 2);
                    document.getElementById('responseBody').textContent = formatted;
                } catch(e) {
                    document.getElementById('responseBody').textContent = text;
                }
                checkForFlag(text);
            })
            .catch(err => {
                document.getElementById('responseBody').textContent = 'Error: ' + err.message;
                document.getElementById('responseStatus').innerHTML = '<span class="status-5xx">Error</span>';
            });
        }

        // Auto-set URL based on level
        <?php if ($level === 1): ?>
            document.getElementById('reqUrl').value = '/api/admin/users';
            document.getElementById('reqMethod').value = 'GET';
        <?php elseif ($level === 2): ?>
            document.getElementById('reqUrl').value = '/api/profile';
            document.getElementById('reqMethod').value = 'GET';
        <?php elseif ($level === 3): ?>
            document.getElementById('reqUrl').value = '/api/profile';
            document.getElementById('reqMethod').value = 'PUT';
            document.getElementById('reqBody').value = '{"email": "alice@newmail.com"}';
        <?php elseif ($level === 4): ?>
            document.getElementById('reqUrl').value = '/api/admin/flag';
            document.getElementById('reqMethod').value = 'GET';
        <?php endif; ?>
    </script>
</body>
</html>
