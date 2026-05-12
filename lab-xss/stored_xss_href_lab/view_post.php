<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

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
trackHit('xss-stored-href');
// ============ END TRACKING ============

if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $_SESSION['flag'] = $flagGen->generate_flag();
}
$flag = $_SESSION['flag'];

// Initialize comments array if it doesn't exist
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}

foreach ($_SESSION['comments'] as $comment) {
    $websiteLower = strtolower($comment['website'] ?? '');
    if (strpos($websiteLower, 'javascript:alert(1)') !== false || strpos($websiteLower, 'alert(1)') !== false) {
        $_SESSION['xss_solved'] = true;
        trackFlag('xss-stored-href', $flag);
        break;
    }
}

// Function to display comments with potential XSS vulnerability in href attribute
function displayComments() {
    if (!isset($_SESSION['comments']) || empty($_SESSION['comments'])) {
        echo '<p>No comments yet. Be the first to comment!</p>';
        return;
    }

    foreach ($_SESSION['comments'] as $comment) {
        $author = htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($comment['email'], ENT_QUOTES, 'UTF-8');
        $website = $comment['website']; // This is the vulnerable field - not properly encoded for href
        $comment_text = htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8');
        $date = $comment['date'];

        // The vulnerability: website is put directly into href attribute
        // Double quotes are HTML-encoded but the javascript: protocol can still be injected
        $website = str_replace('"', '&quot;', $website); // Only double quotes are encoded
        
        echo '<div class="comment">';
        if (!empty($website)) {
            echo '<a href="' . $website . '" class="comment-author">' . $author . '</a>';
        } else {
            echo '<span class="comment-author">' . $author . '</span>';
        }
        echo '<span class="comment-date"> - ' . $date . '</span>';
        echo '<div class="comment-content">' . $comment_text . '</div>';
        echo '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - SecureBlog</title>
    <style>
        .comment {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #3498db;
        }
        .comment-author {
            font-weight: bold;
            color: #2c3e50;
        }
        .comment-date {
            font-size: 12px;
            color: #7f8c8d;
        }
        .comment-content {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div id="comments-container">
        <?php displayComments(); ?>
    </div>

    <script>
        // Check if any comment author links contain javascript:alert(1) to show the flag
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a.comment-author');
            links.forEach(function(link) {
                const href = link.getAttribute('href');
                if (href && (href.includes('javascript:alert(1)') || href.includes('alert(1)'))) {
                    setTimeout(function() {
                        fetch('get_flag.php')
                            .then(response => response.text())
                            .then(flag => {
                                alert('Congratulations! Flag: ' + flag.trim());
                            })
                            .catch(err => {
                                alert('Error fetching flag.');
                            });
                    }, 500);
                }
            });
        });
    </script>
</body>
</html>