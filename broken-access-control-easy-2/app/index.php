<?php
error_reporting(0);

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

trackHit('broken-access-control-easy-2');

$flag = file_exists('/var/flags/flag1.txt') ? trim(file_get_contents('/var/flags/flag1.txt')) : 'IDS{broken_access_control_easy_2_placeholder}';
$users = [
    1 => ['id' => 1, 'username' => 'alice', 'password' => 'alice123', 'role' => 'user', 'email' => 'alice@ids.local', 'notes' => 'Alice profile'],
    2 => ['id' => 2, 'username' => 'bob', 'password' => 'bob456', 'role' => 'user', 'email' => 'bob@ids.local', 'notes' => 'Bob profile'],
    3 => ['id' => 3, 'username' => 'admin', 'password' => 'admin789', 'role' => 'admin', 'email' => 'admin@ids.local', 'notes' => 'Admin profile'],
];

function b64url_encode($data) {
    return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
}
function b64url_decode($data) {
    return json_decode(base64_decode(strtr($data, '-_', '+/')), true);
}
function createToken($payload) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    return b64url_encode($header) . '.' . b64url_encode($payload) . '.' . b64url_encode(['valid' => true]);
}
function parseToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    $payload = b64url_decode($parts[1]);
    return $payload ?: null;
}
function getAuthToken() {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
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

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

if ($uri === '/api/login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['username'], $input['password'])) {
        jsonResponse(['error' => 'Username and password required'], 400);
    }
    foreach ($users as $user) {
        if ($user['username'] === $input['username'] && $user['password'] === $input['password']) {
            jsonResponse([
                'token' => createToken(['user_id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']]),
                'user' => ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']]
            ]);
        }
    }
    jsonResponse(['error' => 'Invalid credentials'], 401);
}

if ($uri === '/api/me' && $method === 'GET') {
    $token = getAuthToken();
    if (!$token) jsonResponse(['error' => 'No token provided'], 401);
    $payload = parseToken($token);
    if (!$payload) jsonResponse(['error' => 'Invalid token'], 401);
    jsonResponse(['user' => $payload]);
}

if (strpos($uri, '/api/') === 0) {
        if (preg_match('#^/api/users/(\d+)$#', $uri, $m) && $method === 'PUT') {
            $token = getAuthToken();
            if (!$token) jsonResponse(['error' => 'Login required'], 401);
            $payload = parseToken($token);
            if (!$payload) jsonResponse(['error' => 'Invalid token'], 401);
            $requestedId = intval($m[1]);
            $user = $users[$requestedId] ?? null;
            if (!$user) jsonResponse(['error' => 'User not found'], 404);
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) jsonResponse(['error' => 'Invalid JSON'], 400);
            $updated = array_merge($user, $input);
            if ($requestedId !== intval($payload['user_id'])) {
                trackFlag('broken-access-control-easy-2', $flag);
                jsonResponse(['message' => 'Profile updated', 'user' => $updated, 'flag' => $flag]);
            }
            jsonResponse(['message' => 'Profile updated', 'user' => $updated]);
        }
    jsonResponse(['error' => 'Endpoint not found'], 404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broken Access Control Easy 2 - Horizontal Privilege Escalation</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');
        * { box-sizing: border-box; }
        body { margin:0; font-family:'Inter', sans-serif; background:#0a0a1a; color:#e5e7eb; min-height:100vh; }
        .wrap { max-width: 980px; margin: 0 auto; padding: 32px 20px 48px; }
        .hero { background: linear-gradient(135deg, #111827, #1f2937); border: 1px solid #243041; border-radius: 16px; padding: 24px; margin-bottom: 20px; }
        .hero h1 { margin: 0 0 8px; font-size: 2rem; }
        .hero p { margin: 0; color: #9ca3af; }
        .card { background: #111827; border: 1px solid #243041; border-radius: 16px; padding: 20px; margin-top: 16px; }
        .tag { display:inline-block; padding:4px 10px; border-radius:999px; font-size:0.75rem; background:rgba(245,158,11,0.12); color:#fbbf24; margin-bottom:12px; }
        .hint { margin-top: 14px; padding: 14px; border-left: 3px solid #f59e0b; background: rgba(245,158,11,0.08); color: #fde68a; border-radius: 10px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        @media (max-width: 860px) { .grid { grid-template-columns:1fr; } }
        label { display:block; margin-bottom:6px; color:#9ca3af; font-size:0.9rem; }
        input, select, textarea { width:100%; background:#0b1020; border:1px solid #2b3647; border-radius:10px; padding:10px 12px; color:#e5e7eb; font-family:'JetBrains Mono', monospace; }
        textarea { min-height: 96px; resize: vertical; }
        button { background: linear-gradient(135deg, #2563eb, #7c3aed); color:white; border:0; border-radius:10px; padding:10px 14px; font-weight:600; cursor:pointer; }
        .secondary { background:#1f2937; border:1px solid #334155; }
        .row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        pre { margin:0; white-space:pre-wrap; word-break:break-word; font-family:'JetBrains Mono', monospace; background:#0b1020; border:1px solid #243041; border-radius:12px; padding:14px; color:#d1d5db; }
        .flag { display:none; margin-top:16px; padding:14px; border-radius:12px; border:1px solid rgba(74,222,128,0.35); background:rgba(16,185,129,0.08); color:#86efac; font-family:'JetBrains Mono', monospace; }
        .flag.show { display:block; }
        code { font-family:'JetBrains Mono', monospace; background:#0b1020; padding:2px 5px; border-radius:6px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="tag">Broken Access Control • Easy Lab</div>
        <h1>Broken Access Control Easy 2 - Horizontal Privilege Escalation</h1>
        <p>Any authenticated user can edit any other user's profile.</p>
    </div>
    <div class="grid">
        <div class="card">
            <strong>Objective</strong>
            <p>Log in as alice and update bob's profile data.</p>
            <div class="hint"><strong>Hint:</strong> Send PUT /api/users/2 with alice's token and a small JSON body.</div>
            <div class="hint" style="margin-top:12px;background:rgba(16,185,129,0.08);border-left-color:#10b981;color:#bbf7d0;">
                <strong>Test credentials:</strong> alice / alice123, bob / bob456, admin / admin789
            </div>
        </div>
        <div class="card">
            <div class="row" style="justify-content:space-between; margin-bottom:12px;">
                <strong>Request playground</strong>
                <button type="button" class="secondary" onclick="quickLogin()">Quick login as alice</button>
            </div>
            <div class="grid" style="grid-template-columns: 120px 1fr; gap: 10px;">
                <div>
                    <label>Method</label>
                    <select id="method"><option>GET</option><option>POST</option><option>PUT</option><option>DELETE</option></select>
                </div>
                <div>
                    <label>URL</label>
                    <input id="url" value="/api/users/2">
                </div>
            </div>
            <div style="margin-top:10px;">
                <label>Authorization token</label>
                <input id="token" placeholder="Paste token here">
            </div>
            <div style="margin-top:10px;">
                <label>JSON body</label>
                <textarea id="body">{"email":"hacked@ids.local"}</textarea>
            </div>
            <div class="row" style="margin-top:12px;">
                <button type="button" onclick="sendRequest()">Send request</button>
            </div>
        </div>
    </div>
    <div class="card">
        <strong>Response</strong>
        <pre id="out">Ready.</pre>
        <div id="flag" class="flag"></div>
    </div>
</div>
<script>
function setFlag(text) {
    const match = text.match(/IDS\{[^}]+\}/);
    if (match) {
        const el = document.getElementById('flag');
        el.textContent = match[0];
        el.classList.add('show');
    }
}
function quickLogin() {
    fetch('/api/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({username: 'alice', password: 'alice123'})
    }).then(async r => {
        const text = await r.text();
        document.getElementById('out').textContent = text;
        try {
            const json = JSON.parse(text);
            if (json.token) document.getElementById('token').value = json.token;
        } catch (e) {}
        setFlag(text);
    }).catch(err => document.getElementById('out').textContent = err.message);
}
function sendRequest() {
    const method = document.getElementById('method').value;
    const url = document.getElementById('url').value;
    const token = document.getElementById('token').value.trim();
    const body = document.getElementById('body').value.trim();
    const headers = {'Content-Type': 'application/json'};
    if (token) headers['Authorization'] = 'Bearer ' + token;
    const opts = {method, headers};
    if (body && ['POST','PUT','PATCH'].includes(method)) opts.body = body;
    fetch(url, opts).then(async r => {
        const text = await r.text();
        document.getElementById('out').textContent = r.status + ' ' + r.statusText + '\n\n' + text;
        setFlag(text);
    }).catch(err => document.getElementById('out').textContent = err.message);
}
</script>
</body>
</html>
