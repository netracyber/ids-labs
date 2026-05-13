<?php
/**
 * LFI entrypoint
 * Medium-hard: validation/normalization mismatch.
 *
 * Key idea:
 * - Validation checks raw input for traversal sequences and a strict allowlist.
 * - Then the code performs URL decoding AFTER validation and reads the file.
 *   This allows bypass with encoded traversal sequences.
 */

session_start();

$flag = $_SESSION['lfi_flag'] ?? 'IDS{6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f}';

function trackFlag($labId, $flag) {
    @file_get_contents("http://tracking-service:8080/api/flag?" . http_build_query([
        'lab' => $labId,
        'flag' => $flag,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]));
}

$raw = $_GET['doc'] ?? 'pages/welcome.txt';

// Basic filter: block obvious traversal and some sensitive keywords (raw input)
$blocked_substrings = [
    '../', '..\\',
    'etc/passwd', 'passwd', 'shadow'
];

$blocked = false;
foreach ($blocked_substrings as $b) {
    if (stripos($raw, $b) !== false) {
        $blocked = true;
        break;
    }
}

// Allowlist: only pages/*.txt
$allowlist_ok = (bool)preg_match('/^pages\/[a-z0-9_%\/\-]+\.txt$/i', $raw);

$doc = $raw;
if (!$blocked && $allowlist_ok) {
    // "Normalization" step happens after validation (the bug):
    $doc = urldecode($raw);
}

$base = __DIR__;
$target = $base . '/' . $doc;

$content = '';
$error = '';
$solved = false;

if ($blocked) {
    $error = 'Blocked by security filter.';
} else {
    if (!file_exists($target)) {
        $error = 'Document not found.';
    } else {
        $content = file_get_contents($target);
        if ($content === false) {
            $error = 'Failed to read document.';
        }
    }
}

// Solve condition: if the resolved target points to our secret training file
$real = realpath($target);
$secret = realpath(__DIR__ . '/private/archive.txt');
if (!$blocked && $content !== '' && $real !== false && $secret !== false && $real === $secret) {
    $solved = true;
    trackFlag('lfi-medium-hard', $flag);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuView | Viewer</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <header>
      <h1>DocuView - Viewer</h1>
      <p class="tagline">Approved documents only</p>
    </header>

    <nav>
      <a href="index.php">Home</a>
      <a class="active" href="view.php">Viewer</a>
    </nav>

    <main>
      <div class="panel">
        <h2>Open document</h2>
        <form method="GET" action="view.php" style="margin-top: 10px;">
          <label for="doc"><strong>doc</strong></label>
          <input id="doc" name="doc" type="text" value="<?php echo htmlspecialchars($raw, ENT_QUOTES, 'UTF-8'); ?>">
          <button type="submit">View</button>
        </form>

        <p style="margin-top: 12px; color: #666;">
          Expected format: <code>pages/&lt;name&gt;.txt</code>
        </p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="panel warning">
          <strong>Error:</strong> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($content)): ?>
        <div class="panel">
          <h2>Preview</h2>
          <pre class="viewer"><?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
      <?php endif; ?>

      <!--
        Viewer Notes:
        - Allowlist is strict.
        - Some clients send encoded paths; we decode for compatibility.
        - The allowlist runs before decoding to avoid confusing users.
        - Legacy clients sometimes double-encode traversal segments.
      -->

    </main>

    <footer>
      <p>&copy; 2026 DocuView | Internal Security Training Lab</p>
    </footer>
  </div>

  <?php if ($solved): ?>
  <script>
    // Flag is only revealed after server-side solve condition.
    alert('Congratulations! LFI Flag: <?php echo addslashes($flag); ?>');
  </script>
  <?php endif; ?>
</body>
</html>
