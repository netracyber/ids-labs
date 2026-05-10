<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
$_SESSION['flag'] = $flag;

// Set the flag cookie (non-httpOnly so it can be stolen via XSS)
if (!isset($_COOKIE['xss_flag'])) {
    setcookie('xss_flag', $flag, time() + 3600, '/', '', false, false);
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
            // Read existing comments from file
            $commentsFile = 'comments.txt';
            $comments = [];
            $maxAge = 120; // Auto-delete comments older than 2 minutes

            if (file_exists($commentsFile)) {
                $raw = @unserialize(file_get_contents($commentsFile));
                if (is_array($raw)) {
                    // Clean up old comments (older than 2 minutes)
                    $now = time();
                    foreach ($raw as $key => $entry) {
                        // Handle both old format (plain string) and new format (array with timestamp)
                        if (is_array($entry) && isset($entry['time'])) {
                            if (($now - $entry['time']) > $maxAge) {
                                unset($raw[$key]);
                            }
                        } elseif (is_string($entry)) {
                            // Old format - add timestamp as now (give grace period)
                            $raw[$key] = ['text' => $entry, 'time' => $now];
                        }
                    }
                    $comments = array_values($raw);
                    file_put_contents($commentsFile, serialize($comments));
                }
            }

            // Check for XSS only in the newly submitted comment (for flag detection)
            $newXssDetected = false;
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

            // Display existing comments or show "No comments yet" if empty
            if (empty($comments)) {
                echo '<p>No comments yet. Be the first to comment!</p>';
            } else {
                // Display existing comments
                foreach ($comments as $entry) {
                    $text = is_array($entry) ? ($entry['text'] ?? '') : $entry;
                    echo '<div class="comment">' . $text . '</div>';
                }
            }

            // Handle new comment submission
            if ($_POST && isset($_POST['comment'])) {
                $newComment = $_POST['comment'];
                $comments[] = ['text' => $newComment, 'time' => time()];

                // Check if the new comment contains XSS patterns (for flag detection)
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $newComment)) {
                        $newXssDetected = true;
                        break;
                    }
                }

                // Save comments to file
                file_put_contents($commentsFile, serialize($comments));

                // Redirect to prevent duplicate submissions
                // Only show the alert if XSS was detected in the new comment
                if ($newXssDetected) {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?xss_success=1');
                    exit;
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
            }

            // Handle clear all comments
            if ($_POST && isset($_POST['clear_all'])) {
                file_put_contents($commentsFile, serialize([]));
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }

            // Check if we just submitted an XSS payload (indicated by the query parameter)
            $xssDetectedFromSubmission = isset($_GET['xss_success']) && $_GET['xss_success'] == '1';
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