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
trackHit('broken-access');

// ============ CONFIGURATION ============
$flags = [
    1 => file_exists('/var/flags/flag1.txt') ? trim(file_get_contents('/var/flags/flag1.txt')) : 'IDS{flag1_placeholder}',
    2 => file_exists('/var/flags/flag2.txt') ? trim(file_get_contents('/var/flags/flag2.txt')) : 'IDS{flag2_placeholder}',
    3 => file_exists('/var/flags/flag3.txt') ? trim(file_get_contents('/var/flags/flag3.txt')) : 'IDS{flag3_placeholder}',
    4 => file_exists('/var/flags/flag4.txt') ? trim(file_get_contents('/var/flags/flag4.txt')) : 'IDS{flag4_placeholder}',
];

$levels = [
    1 => ['name' => 'IDOR - Insecure Direct Object Reference', 'difficulty' => 'Easy', 'color' => '#10b981'],
    2 => ['name' => 'Privilege Escalation - Horizontal', 'difficulty' => 'Medium', 'color' => '#f59e0b'],
    3 => ['name' => 'Privilege Escalation - Vertical', 'difficulty' => 'Medium', 'color' => '#f59e0b'],
    4 => ['name' => 'Forceful Browsing', 'difficulty' => 'Hard', 'color' => '#ef4444'],
];

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 4) $level = 1;

// ============ USERS DATABASE ============
$users = [
    1 => ['id' => 1, 'username' => 'alice', 'password' => 'alice123', 'role' => 'user', 'email' => 'alice@ids.local', 'ssn' => '123-45-6789', 'data' => 'Alice private data'],
    2 => ['id' => 2, 'username' => 'bob', 'password' => 'bob456', 'role' => 'user', 'email' => 'bob@ids.local', 'ssn' => '234-56-7890', 'data' => 'Bob private data'],
    3 => ['id' => 3, 'username' => 'charlie', 'password' => 'charlie789', 'role' => 'user', 'email' => 'charlie@ids.local', 'ssn' => '345-67-8901', 'data' => 'Charlie private data'],
    4 => ['id' => 4, 'username' => 'admin', 'password' => 'admin321', 'role' => 'admin', 'email' => 'admin@ids.local', 'ssn' => '456-78-9012', 'data' => 'Admin private data'],
];

// ============ TOKEN HELPER ============
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
    $payload = base64url_decode($parts[1]);
    if (!$payload) return null;
    return $payload;
}
function getAuthToken() {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}
function getAuthUser($token) {
    $payload = parseToken($token);
    if (!$payload) return null;
    return $payload;
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

if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// API routes
if (strpos($uri, '/api/') === 0) {
    // POST /api/login
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

    // GET /api/me
    if ($uri === '/api/me' && $method === 'GET') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'No token provided'], 401);
        $user = getAuthUser($token);
        if (!$user) jsonResponse(['error' => 'Invalid token'], 401);
        jsonResponse(['user' => $user]);
    }

    // ============ LEVEL 1: IDOR ============
    // GET /api/users/{id} - No ownership check
    if (preg_match('#^/api/users/(\d+)$#', $uri, $matches) && $method === 'GET') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'Login required'], 401);
        $authUser = getAuthUser($token);
        if (!$authUser) jsonResponse(['error' => 'Invalid token'], 401);

        $requestedId = intval($matches[1]);
        if (!isset($users[$requestedId])) {
            jsonResponse(['error' => 'User not found'], 404);
        }

        $targetUser = $users[$requestedId];

        // VULNERABILITY: No check if authUser['user_id'] == requestedId
        // Returns any user's data regardless of who is logged in
        if ($requestedId !== $authUser['user_id']) trackFlag('broken-access', $flags[1]);
        jsonResponse([
            'id' => $targetUser['id'],
            'username' => $targetUser['username'],
            'email' => $targetUser['email'],
            'ssn' => $targetUser['ssn'],
            'role' => $targetUser['role'],
            'private_data' => $targetUser['data'],
            'flag' => ($requestedId !== $authUser['user_id']) ? $flags[1] : null
        ]);
    }

    // ============ LEVEL 2: Horizontal Privilege Escalation ============
    // PUT /api/users/{id} - Can edit other users' data
    if (preg_match('#^/api/users/(\d+)$#', $uri, $matches) && $method === 'PUT') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'Login required'], 401);
        $authUser = getAuthUser($token);
        if (!$authUser) jsonResponse(['error' => 'Invalid token'], 401);

        $requestedId = intval($matches[1]);
        if (!isset($users[$requestedId])) {
            jsonResponse(['error' => 'User not found'], 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Invalid JSON'], 400);

        // VULNERABILITY: No ownership check - any authenticated user can edit any other user
        $targetUser = $users[$requestedId];
        $updated = array_merge($targetUser, $input);

        $result = [
            'message' => 'User updated successfully',
            'user' => [
                'id' => $updated['id'],
                'username' => $updated['username'],
                'email' => $updated['email']
            ]
        ];

        // Give flag if editing another user's data
        if ($requestedId !== $authUser['user_id']) {
            $result['flag'] = $flags[2];
            trackFlag('broken-access', $flags[2]);
        }

        jsonResponse($result);
    }

    // ============ LEVEL 3: Vertical Privilege Escalation ============
    // POST /api/admin/flag - Should only be accessible by admins
    if ($uri === '/api/admin/flag' && $method === 'POST') {
        $token = getAuthToken();
        if (!$token) jsonResponse(['error' => 'Login required'], 401);
        $authUser = getAuthUser($token);
        if (!$authUser) jsonResponse(['error' => 'Invalid token'], 401);

        // VULNERABILITY: Only checks if user is logged in, doesn't properly check role
        // The role check is flawed - it checks existence of 'role' key instead of value
        if (isset($authUser['role'])) {
            // BUG: Should be $authUser['role'] === 'admin' but just checks if key exists
            trackFlag('broken-access', $flags[3]);
            jsonResponse([
                'message' => 'Admin flag retrieved',
                'flag' => $flags[3],
                'admin_notes' => 'System maintenance scheduled for next week'
            ]);
        }

        jsonResponse(['error' => 'Access denied'], 403);
    }

    // ============ LEVEL 4: Forceful Browsing ============
    // GET /api/admin/debug/flag - Hidden endpoint, no auth at all
    if ($uri === '/api/admin/debug/flag' && $method === 'GET') {
        // VULNERABILITY: No authentication required at all for this hidden debug endpoint
        trackFlag('broken-access', $flags[4]);
        jsonResponse([
            'debug_mode' => true,
            'flag' => $flags[4],
            'system_info' => [
                'php_version' => phpversion(),
                'server_time' => date('Y-m-d H:i:s'),
                'env' => 'development'
            ]
        ]);
    }

    // Catch-all
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
    <title>Broken Access Control Lab - IDS Cybersecurity</title>
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
            background: linear-gradient(135deg, #f97316, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }
        .header p {
            color: #9ca3af;
            font-size: 1em;
        }

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
            border-color: rgba(249,115,22,0.5);
            color: #e0e0e0;
        }
        .level-tab.active {
            background: rgba(249,115,22,0.15);
            border-color: #f97316;
            color: #f97316;
        }
        .level-tab .diff {
            font-size: 0.75em;
            opacity: 0.7;
        }

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

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 900px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        .clue-box {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .clue-box h3 {
            color: #ef4444;
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
            color: #f87171;
        }

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
            border-color: #f97316;
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
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-secondary {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #9ca3af;
        }
        .btn-secondary:hover { border-color: rgba(255,255,255,0.3); color: #e0e0e0; }

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

        .hint-section { margin-top: 20px; }
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

        .user-quick-btns {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .user-quick-btns .btn {
            font-size: 0.78em;
            padding: 5px 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Broken Access Control Lab</h1>
        <p>Exploit IDOR, privilege escalation, and forceful browsing vulnerabilities</p>
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
            <p>Access another user's private data by changing the user ID in the API request. You are logged in as user 1 (alice), but you need to access user 2's (bob) data.<br>
            Login as alice: <code>POST /api/login {"username":"alice","password":"alice123"}</code><br>
            Then try: <code>GET /api/users/2</code> with alice's token.</p>
        </div>
        <?php elseif ($level === 2): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Edit another user's profile data. Login as alice (user 1), then modify bob's (user 3) data.<br>
            Login: <code>POST /api/login {"username":"alice","password":"alice123"}</code><br>
            Then: <code>PUT /api/users/3</code> with alice's token and a JSON body.</p>
        </div>
        <?php elseif ($level === 3): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Access an admin-only endpoint as a regular user. The access control check is flawed.<br>
            Login as a regular user: <code>POST /api/login {"username":"alice","password":"alice123"}</code><br>
            Then try: <code>POST /api/admin/flag</code> with alice's token.</p>
        </div>
        <?php elseif ($level === 4): ?>
        <div class="clue-box">
            <h3>Objective</h3>
            <p>Find and access a hidden debug endpoint that was left in production. No authentication is needed, but you need to guess the URL.<br>
            Think about common debug/admin paths... Try common patterns like <code>/api/admin/debug/</code></p>
        </div>
        <?php endif; ?>

        <!-- Credentials -->
        <div class="creds-box">
            <h4>Test Credentials</h4>
            <table>
                <tr><td>alice</td><td>: alice123</td><td>(user, id:1)</td></tr>
                <tr><td>bob</td><td>: bob456</td><td>(user, id:2)</td></tr>
                <tr><td>charlie</td><td>: charlie789</td><td>(user, id:3)</td></tr>
                <tr><td>admin</td><td>: admin321</td><td>(admin, id:4)</td></tr>
            </table>
        </div>

        <div class="content-grid">
            <!-- Request Panel -->
            <div class="panel">
                <div class="panel-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
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
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <button class="btn btn-primary" onclick="sendRequest()">Send Request</button>
                        <span style="font-size:0.8em;color:#6b7280;">Quick Login:</span>
                        <div class="user-quick-btns">
                            <button class="btn btn-secondary" onclick="quickLogin('alice','alice123')">alice</button>
                            <button class="btn btn-secondary" onclick="quickLogin('bob','bob456')">bob</button>
                            <button class="btn btn-secondary" onclick="quickLogin('charlie','charlie789')">charlie</button>
                            <button class="btn btn-secondary" onclick="quickLogin('admin','admin321')">admin</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Panel -->
            <div class="panel">
                <div class="panel-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
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
                    <strong>Hint:</strong> This is an IDOR vulnerability. Login as alice, then change the user ID in the URL from <code>/api/users/1</code> to <code>/api/users/2</code>. The server doesn't verify that the requesting user owns the data.
                <?php elseif ($level === 2): ?>
                    <strong>Hint:</strong> Login as alice (user 1), then send a PUT request to <code>/api/users/3</code> with a JSON body like <code>{"email": "hacked@ids.local"}</code>. The server allows any authenticated user to modify any other user's data.
                <?php elseif ($level === 3): ?>
                    <strong>Hint:</strong> The admin endpoint checks if the <code>role</code> field exists in the token, not if it equals "admin". Since every user's token contains a <code>role</code> field, any logged-in user can access the admin endpoint at <code>POST /api/admin/flag</code>.
                <?php elseif ($level === 4): ?>
                    <strong>Hint:</strong> The endpoint is at <code>/api/admin/debug/flag</code>. It's a hidden debug endpoint that was left in production with no authentication at all. Try <code>GET /api/admin/debug/flag</code> without any token.
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

        function quickLogin(username, password) {
            fetch(proxyUrl('/api/login'), {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({username: username, password: password})
            })
            .then(r => {
                const statusClass = r.status < 300 ? 'status-2xx' : 'status-4xx';
                document.getElementById('responseStatus').innerHTML = '<span class="' + statusClass + '">' + r.status + ' ' + r.statusText + '</span>';
                return r.text();
            })
            .then(text => {
                try {
                    const json = JSON.parse(text);
                    document.getElementById('responseBody').textContent = JSON.stringify(json, null, 2);
                    if (json.token) {
                        currentToken = json.token;
                        document.getElementById('reqToken').value = json.token;
                    }
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
                    document.getElementById('responseBody').textContent = JSON.stringify(json, null, 2);
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

        // Auto-set based on level
        <?php if ($level === 1): ?>
            document.getElementById('reqUrl').value = '/api/users/1';
            document.getElementById('reqMethod').value = 'GET';
        <?php elseif ($level === 2): ?>
            document.getElementById('reqUrl').value = '/api/users/3';
            document.getElementById('reqMethod').value = 'PUT';
            document.getElementById('reqBody').value = '{"email": "new@example.com"}';
        <?php elseif ($level === 3): ?>
            document.getElementById('reqUrl').value = '/api/admin/flag';
            document.getElementById('reqMethod').value = 'POST';
        <?php elseif ($level === 4): ?>
            document.getElementById('reqUrl').value = '/api/admin/debug/flag';
            document.getElementById('reqMethod').value = 'GET';
        <?php endif; ?>
    </script>
</body>
</html>
