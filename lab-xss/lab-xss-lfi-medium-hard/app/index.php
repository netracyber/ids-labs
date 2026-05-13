<?php
/**
 * LFI Lab - Medium-Hard (Standalone)
 * Landing page + session-based flag setup
 */

// ============ TRACKING (same pattern as other labs) ============
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
trackHit('lfi-medium-hard');
// ============ END TRACKING ============

$flag_file = '/tmp/flag.txt';
$flag_script = '/usr/local/bin/generate_flag.py';

if (!file_exists($flag_file)) {
    $flag = shell_exec("python3 $flag_script 2>/dev/null | grep -oP 'IDS\\{[^}]+\\}' || echo 'IDS{a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6}'");
    if (empty($flag)) {
        $flag = 'IDS{b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7}';
    }
    file_put_contents($flag_file, trim($flag));
} else {
    $flag = trim(file_get_contents($flag_file));
}

session_start();
$_SESSION['lfi_flag'] = $flag;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuView | Internal Viewer</title>
  <link rel="stylesheet" href="style.css">
  <script>
    console.log('DocuView initialized');
    console.log('Tip: validation and usage are not always the same step.');
  </script>
</head>
<body>
  <div class="container">
    <header>
      <h1>DocuView - Internal Document Viewer</h1>
      <p class="tagline">Read-only viewer for approved documents</p>
    </header>

    <nav>
      <a class="active" href="index.php">Home</a>
      <a href="view.php">Viewer</a>
    </nav>

    <main>
      <div class="panel">
        <h2>About</h2>
        <p>This tool is restricted to <strong>approved docs only</strong>. For security, paths are sanitized and only <code>.txt</code> files are allowed.</p>
        <div class="notice" style="margin-top: 12px;">
          Security Notice: traversal patterns and sensitive filenames are blocked by our filter.
        </div>
      </div>

      <div class="panel">
        <h2>Open a document</h2>
        <p>Try one of the samples:</p>
        <ul style="margin-left: 18px; margin-top: 10px; line-height: 1.7;">
          <li><a href="view.php?doc=pages/welcome.txt">pages/welcome.txt</a></li>
          <li><a href="view.php?doc=pages/help.txt">pages/help.txt</a></li>
        </ul>

        <form method="GET" action="view.php" style="margin-top: 14px;">
          <label for="doc"><strong>doc</strong> parameter</label>
          <input id="doc" name="doc" type="text" placeholder="pages/welcome.txt">
          <button type="submit">View</button>
        </form>
      </div>

      <!--
        INTERNAL NOTE:
        - Only allow .txt
        - Block traversal: ../ and ..\\
        - Do not allow direct access to sensitive keywords (passwd, shadow)

        QA reminder: our allowlist check runs on the raw input to preserve user intent.
        Encoded paths are normalized after validation for compatibility.
        Some legacy clients double-encode reserved characters.
      -->

      <div class="panel warning">
        <strong>Operational hint:</strong> This viewer uses a legacy path-check for compatibility. Some clients send URL-encoded paths.
      </div>
    </main>

    <footer>
      <p>&copy; 2026 DocuView | Internal Security Training Lab</p>
    </footer>
  </div>
</body>
</html>
