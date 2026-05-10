<?php
// Initialize comments array
session_start();

// Create comments array if it doesn't exist
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $website = isset($_POST['website']) ? trim($_POST['website']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $date = date('F j, Y');

    // Store the comment (vulnerable to stored XSS)
    $new_comment = [
        'author' => $author,
        'email' => $email,
        'website' => $website,  // This will be used in href attribute
        'comment' => $comment,
        'date' => $date
    ];

    $_SESSION['comments'][] = $new_comment;

    // Redirect back to the page to prevent duplicate submissions
    header('Location: index.html');
    exit();
}
?>