<?php
/**
 * Upload Page - Placeholder
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload | PixelPerfect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📤 Upload</h1>
            <p class="tagline">Image Upload System</p>
        </header>

        <nav>
            <a href="index.php">Home</a>
            <a href="gallery.php">Gallery</a>
            <a href="upload.php" class="active">Upload</a>
        </nav>

        <main>
            <section class="info-box">
                <h2>Upload Feature</h2>
                <p>The upload module is currently under development.</p>
                <p>Please visit the <a href="gallery.php" style="color: #667eea;">Gallery</a> page to test image display with URL parameters.</p>
            </section>

            <section class="debug-section">
                <h3>Coming Soon</h3>
                <ul>
                    <li>Image file upload</li>
                    <li>Auto-generated thumbnails</li>
                    <li>Batch upload support</li>
                </ul>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 PixelPerfect Gallery. All rights reserved.</p>
        </footer>
    </div>

    <script>
        console.log('Upload page is a placeholder. Try gallery.php for XSS challenges.');
        console.log('Focus on event handler attribute injection vulnerabilities.');
    </script>
</body>
</html>
