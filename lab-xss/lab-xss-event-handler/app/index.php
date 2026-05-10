<?php
/**
 * Reflected XSS Lab - Event Handler Attribute
 * Main Entry Point
 */

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
trackHit('xss-event-handler');
// ============ END TRACKING ============

// Static flag for this lab
$flag = "IDS{3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8a}";

// Store flag in session for later validation
session_start();
$_SESSION['xss_flag'] = $flag;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelPerfect Gallery | Image Showcase</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Application configuration
        const GALLERY_CONFIG = {
            name: 'PixelPerfect Gallery',
            version: '3.1.0',
            maxImages: 20,
            // Supported URL parameters
            params: {
                image: 'Image URL or filename',
                caption: 'Image caption text',
                alt: 'Alternative text for accessibility',
                // Legacy parameters with different validation
                src: 'Source URL (minimal filtering)',
                fallback: 'Fallback image URL'
            }
        };

        console.log('%c' + GALLERY_CONFIG.name + ' v' + GALLERY_CONFIG.version, 'font-size: 14px; font-weight: bold; color: #ff6b6b;');
        console.log('Available parameters:', Object.keys(GALLERY_CONFIG.params));

        // Security notice
        console.log('Security: All inputs are sanitized with HTML entity encoding.');
        console.log('Event handlers are protected with attribute validation.');

        // Random hint
        const hints = [
            "HTML attributes use quotes: single (') or double (\")",
            "Event handlers contain JavaScript code: onclick, onload, onerror",
            "To break out of an attribute, close the quote first",
            "After the attribute, you may need to close the HTML tag",
            "Different attributes have different contexts and rules"
        ];
        console.log('%c💡 Tip: ' + hints[Math.floor(Math.random() * hints.length)], 'color: #888; font-style: italic;');
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>🖼️ PixelPerfect Gallery</h1>
            <p class="tagline">Professional image showcase platform</p>
        </header>

        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="gallery.php">Gallery</a>
            <a href="upload.php">Upload</a>
            <a href="#" onclick="showHelp(); return false;">Help</a>
        </nav>

        <main>
            <section class="welcome">
                <h2>Welcome to PixelPerfect!</h2>
                <p>The ultimate platform for showcasing and managing your image collections.</p>
            </section>

            <section class="features">
                <h3>Gallery Features</h3>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4>📸 Dynamic Image Loading</h4>
                        <p>Load images from URLs with custom captions and alt text.</p>
                        <a href="gallery.php" class="btn">Open Gallery</a>
                    </div>
                    <div class="feature-card">
                        <h4>⚙️ Customizable Display</h4>
                        <p>Configure image attributes and event handlers.</p>
                        <a href="gallery.php" class="btn">Configure</a>
                    </div>
                    <div class="feature-card">
                        <h4>🔍 Error Handling</h4>
                        <p>Automatic fallback images and error notifications.</p>
                        <a href="gallery.php" class="btn">View Demo</a>
                    </div>
                </div>
            </section>

            <section class="info-box">
                <h3>📖 How to Use</h3>
                <ol>
                    <li>Visit the <strong>Gallery</strong> to view images</li>
                    <li>Use URL parameters to customize image display</li>
                    <li>Test different image sources and error handling</li>
                </ol>
                <p class="note">
                    <strong>Pro Tip:</strong> Try accessing the gallery with custom parameters like
                    <code>?image=photo.jpg&caption=MyPhoto</code>
                </p>
            </section>

            <!-- Hidden clues -->
            <!--
            ========================================================================
            PIXELPERFECT GALLERY - INTERNAL DOCUMENTATION
            ========================================================================

            Image Loading System:
            ----------------------
            The gallery uses HTML img tags with various attributes populated
            from URL parameters. Event handlers are used for error handling
            and user interactions.

            Parameter Processing:
            ----------------------
            - image: Main image source URL
            - caption: Image caption text
            - alt: Alt text for accessibility
            - fallback: Fallback image on error
            - src: Legacy image source (minimal validation)

            Event Handler Usage:
            ---------------------
            The gallery implements these event handlers:
            - onerror: Triggered when image fails to load
            - onload: Triggered when image loads successfully
            - onclick: Triggered when user clicks image
            - onmouseover: Triggered when user hovers over image

            Security Implementation:
            ------------------------
            - HTML entity encoding for text attributes (caption, alt)
            - URL validation for image sources
            - Event handler values are sanitized

            Known Edge Cases:
            ------------------
            - Some attributes may use different quote styles
            - Legacy parameters have less strict validation
            - Event handler sanitization may be inconsistent

            Attribute Context Examples:
            ---------------------------
            <img src="[user_input]" onerror="[user_input]">
            <img alt="[user_input]" src="default.jpg">
            <div title="[user_input]">Content</div>

            ========================================================================
            -->

            <section class="tips">
                <h3>💡 Testing Tips</h3>
                <ul class="tips-list">
                    <li>Event handlers contain JavaScript code - what syntax is valid?</li>
                    <li>Try injecting into different attributes to see how they're handled</li>
                    <li>The onerror event triggers when an image fails to load</li>
                    <li>You can use invalid image URLs to trigger error handlers</li>
                    <li>Some attributes use single quotes, others use double quotes</li>
                </ul>
            </section>

            <section class="security-info">
                <h3>🔒 Security Information</h3>
                <p>This application implements several security measures:</p>
                <ul>
                    <li>HTML entity encoding for user input in attributes</li>
                    <li>URL validation for image sources</li>
                    <li>Event handler sanitization</li>
                    <li>Content Security Policy headers</li>
                </ul>
                <p class="warning">
                    Note: Event handlers execute JavaScript in the browser context.
                    Always validate and sanitize user input before placing it in attributes.
                </p>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 PixelPerfect Gallery. All rights reserved.</p>
            <p class="version">Version 3.1.0 | Event Handler Injection Test Environment</p>
        </footer>
    </div>

    <script>
        function showHelp() {
            alert('PixelPerfect Gallery Help:\n\n1. Visit gallery.php to view images\n2. Use ?image=url to load images\n3. Use ?caption=text to add captions\n4. Use ?alt=text for alt text\n\nExample:\ngallery.php?image=photo.jpg&caption=Sunset');
        }

        // Easter egg
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c Event Handler Tip:', 'background: #222; color: #ff6b6b; padding: 3px;',
                'HTML attributes can contain event handlers like onerror, onload, onclick.');
            console.log('These attributes contain JavaScript code that executes when the event occurs.');
            console.log('Example: <img src="x" onerror="alert(1)">');
        });

        // Random attribute tip
        const attrTips = [
            "HTML attributes are enclosed in quotes: single (') or double (\")",
            "To escape an attribute, close the quote: \" to end the attribute value",
            "After closing the attribute, you can add new attributes or close the tag",
            "Event handlers can access JavaScript functions and global variables",
            "The onerror event fires when an image fails to load - use an invalid URL!"
        ];
        console.log('%c💡 Attribute Tip: ' + attrTips[Math.floor(Math.random() * attrTips.length)], 'color: #666;');
    </script>

    <!--
    ========================================================================
    DEBUG INFO - GALLERY SYSTEM
    ========================================================================

    Gallery URL Examples:
    ---------------------
    gallery.php?image=photo.jpg
    gallery.php?image=photo.jpg&caption=My%20Photo
    gallery.php?image=invalid.jpg&alt=Description
    gallery.php?src=x.jpg&fallback=y.jpg (legacy parameters)

    Attribute Injection Points:
    ---------------------------
    - img src attribute: <img src="[USER_INPUT]">
    - img alt attribute: <img alt="[USER_INPUT]">
    - img onerror attribute: <img onerror="[USER_INPUT]">
    - div title attribute: <div title="[USER_INPUT]">

    Escaping Implementation:
    -----------------------
    function escapeForAttribute($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    Challenge:
    ----------
    Can you break out of an event handler attribute and execute arbitrary code?
    Remember: Event handlers contain JavaScript code, not HTML!

    ========================================================================
    -->
</body>
</html>
