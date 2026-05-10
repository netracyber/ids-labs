<?php
/**
 * Settings Page - Placeholder
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | DevTools Pro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ Settings</h1>
            <p class="tagline">Application Configuration</p>
        </header>

        <nav>
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="settings.php" class="active">Settings</a>
        </nav>

        <main>
            <section class="info-box">
                <h2>Settings Page</h2>
                <p>The settings module is currently under development.</p>
                <p>Please visit the <a href="profile.php" style="color: #00ff00;">Profile</a> page for customization options.</p>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 DevTools Pro. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Redirect hint
        console.log('Settings page is a placeholder. Try profile.php for XSS challenges.');
    </script>
</body>
</html>
