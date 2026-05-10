<?php
// POST processing MUST happen before any HTML output
$commentsFile = __DIR__ . '/comments.txt';
$comments = [];
$maxAge = 120;

// Load existing comments
if (file_exists($commentsFile)) {
    $raw = @unserialize(file_get_contents($commentsFile));
    if (is_array($raw)) {
        $now = time();
        foreach ($raw as $key => $entry) {
            if (is_array($entry) && isset($entry['time'])) {
                if (($now - $entry['time']) > $maxAge) {
                    unset($raw[$key]);
                }
            } elseif (is_string($entry)) {
                $raw[$key] = ['text' => $entry, 'time' => $now];
            }
        }
        $comments = array_values($raw);
        file_put_contents($commentsFile, serialize($comments));
    }
}

// XSS detection patterns
$xssPatterns = [
    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
    '/on\w+\s*=/i',
    '/<img[^>]*onerror\s*=/i',
    '/<svg[^>]*onload\s*=/i',
    '/javascript:/i',
    '/<iframe/i',
    '/<object/i',
    '/<embed/i',
    '/<div[^>]*onclick\s*=/i',
    '/<a[^>]*onmouseover\s*=/i'
];

// Handle clear all comments (before any output)
if ($_POST && isset($_POST['clear_all'])) {
    file_put_contents($commentsFile, serialize([]));
    $comments = [];
    // Don't redirect - just continue rendering
}

// Handle new comment submission (before any output)
$xssDetectedFromSubmission = false;

if ($_POST && isset($_POST['comment'])) {
    $newComment = $_POST['comment'];
    $comments[] = ['text' => $newComment, 'time' => time()];

    foreach ($xssPatterns as $pattern) {
        if (preg_match($pattern, $newComment)) {
            $xssDetectedFromSubmission = true;
            break;
        }
    }

    file_put_contents($commentsFile, serialize($comments));
    // Don't redirect - just continue rendering with the flag set
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Post - Stored XSS Lab</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .post {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .comments-section {
            margin-top: 30px;
        }
        .comment {
            padding: 15px;
            margin: 10px 0;
            background-color: #e9ecef;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .comment-form {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .instructions {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
        }
        .xss-success {
            margin-top: 20px;
            padding: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Blog Post: The Importance of Web Security</h1>

        <div class="post">
            <h2>Blog Post: The Importance of Web Security</h2>
            <p>This blog allows users to comment, but has a vulnerability that allows for stored cross-site scripting attacks.</p>
        </div>

        <div class="comments-section">
            <h3>Comments</h3>
            <?php
            // Display existing comments or show "No comments yet" if empty
            if (empty($comments)) {
                echo '<p>No comments yet. Be the first to comment!</p>';
            } else {
                foreach ($comments as $entry) {
                    $text = is_array($entry) ? ($entry['text'] ?? '') : $entry;
                    echo '<div class="comment">' . $text . '</div>';
                }
            }
            ?>

            <?php if ($xssDetectedFromSubmission): ?>
            <div class="xss-success">
                <p>You triggered XSS! Now try to steal the cookie.</p>
                <p>The flag is stored in a cookie named <code>xss_flag</code>. Use your XSS payload to exfiltrate it.</p>
            </div>
            <?php endif; ?>

            <div class="comment-form">
                <h4>Add a Comment</h4>
                <form method="POST">
                    <textarea name="comment" placeholder="Enter your comment here..."></textarea>
                    <button type="submit">Submit Comment</button>
                </form>
                <form method="POST" style="margin-top: 10px; display: inline;">
                    <input type="hidden" name="clear_all" value="1">
                    <button type="submit" style="background-color: #dc3545; font-size: 12px; padding: 6px 12px;">Clear All Comments</button>
                </form>
                <p style="font-size: 11px; color: #999; margin-top: 8px;">Comments are automatically deleted after 2 minutes.</p>
            </div>
        </div>

    </div>
</body>
</html>
