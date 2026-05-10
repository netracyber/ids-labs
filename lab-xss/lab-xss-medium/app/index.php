<?php
/**
 * Reflected XSS Lab - Medium Difficulty
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
trackHit('xss-medium');
// ============ END TRACKING ============

// Generate or retrieve flag from flag generator
$flag_file = '/tmp/flag.txt';
$flag_script = '/usr/local/bin/generate_flag.py';

if (!file_exists($flag_file)) {
    // Generate flag using Python script
    $flag = shell_exec("python3 $flag_script 2>/dev/null | grep -oP 'IDS\\{[^}]+\\}' || echo 'IDS{fallback_flag_for_demo}'");
    if (empty($flag)) {
        $flag = 'IDS{flag_generation_failed}';
    }
    file_put_contents($flag_file, trim($flag));
} else {
    $flag = trim(file_get_contents($flag_file));
}

// Store flag in session for later validation
session_start();
$_SESSION['xss_flag'] = $flag;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search | SecureShop</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Hidden configuration - some values might be useful for advanced users
        const CONFIG = {
            searchEndpoint: 'search.php',
            maxResults: 10,
            debugMode: false,
            encoding: 'utf-8',
            // Legacy parameter name - sometimes old code has unexpected behavior
            legacyParams: ['q', 'query', 'term', 'search', 'find']
        };

        // Client-side validation (never trust client-side only!)
        function validateSearch(input) {
            // This is just UI feedback - real security happens server-side
            const dangerous = ['<script', 'javascript:', 'onerror=', 'onload='];
            for (let pattern of dangerous) {
                if (input.toLowerCase().includes(pattern)) {
                    return false;
                }
            }
            return true;
        }

        // Some users found that case sensitivity matters in various systems
        const SYSTEM_HINT = "Case sensitivity: testing vs Testing vs TESTING";

        console.log("Welcome to SecureShop search system");
        console.log("System initialized with encoding: " + CONFIG.encoding);
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔒 SecureShop Product Search</h1>
            <p class="tagline">Your trusted marketplace since 2024</p>
        </header>

        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="search.php">Advanced Search</a>
            <a href="#" onclick="showHelp(); return false;">Help</a>
        </nav>

        <main>
            <section class="search-section">
                <h2>Search Our Products</h2>
                <p class="info-text">Enter a product name or keyword to search our catalog.</p>

                <form action="search.php" method="GET" class="search-form">
                    <div class="input-group">
                        <label for="query">Product Name:</label>
                        <input type="text" id="query" name="query" placeholder="e.g., laptop, phone, tablet...">
                        <button type="submit" class="btn-search">🔍 Search</button>
                    </div>
                    <p class="hint">Try searching for any product - results may vary!</p>
                </form>
            </section>

            <section class="features">
                <h3>Why Choose SecureShop?</h3>
                <div class="feature-grid">
                    <div class="feature">
                        <h4>🛡️ Secure Platform</h4>
                        <p>Our advanced security filters protect against common web vulnerabilities.</p>
                    </div>
                    <div class="feature">
                        <h4>⚡ Fast Search</h4>
                        <p>Lightning-fast product lookup with real-time filtering.</p>
                    </div>
                    <div class="feature">
                        <h4>🔍 Advanced Technology</h4>
                        <p>State-of-the-art encoding and sanitization for your safety.</p>
                    </div>
                </div>
            </section>

            <!-- Hidden clues for observant users -->
            <!--
            ========================================================================
            SECURITY NOTICE: System employs XSS filtering
            ========================================================================

            Filter Configuration (v2.4.1):
            - Script tag removal: ENABLED
            - Protocol filtering: ENABLED
            - Event handler blocking: ENABLED

            Note: Some HTML entities are properly decoded before rendering.
            Character encoding inconsistencies may exist in legacy components.

            Parameter mapping:
            - Primary: 'query' (validated)
            - Legacy: 'q' (minimal validation - for backward compatibility)

            Remember: Different contexts require different approaches.
            What works in attributes might not work in text content.

            ========================================================================
            -->

            <section class="tips">
                <h3>💡 Search Tips</h3>
                <ul>
                    <li>Use specific product names for better results</li>
                    <li>Some older endpoints may have different validation rules</li>
                    <li>HTML5 introduced many new elements - some with interesting attributes</li>
                    <li>Multiple encodings can represent the same character differently</li>
                </ul>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 SecureShop. All rights reserved.</p>
            <p class="small-text">Last security audit: 2024-01-15 | Filter version: 2.4.1-stable</p>
        </footer>
    </div>

    <script>
        function showHelp() {
            alert('Search Help:\n\n1. Enter your search term in the box\n2. Click Search or press Enter\n3. Browse the results\n\nNote: All searches are logged for security purposes.');
        }

        // Easter egg for curious minds
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c Security Tip: ', 'background: #222; color: #bada55', 'Always validate and encode user input on both client and server side.');
        });

        // Random hint display (changes on refresh)
        const hints = [
            "HTML has many tags beyond script and img.",
            "Some filters are case-sensitive, others aren't.",
            "Attributes can be injected in unexpected places.",
            "Entity encoding happens at different stages of processing.",
            "Legacy code sometimes has fewer security checks."
        ];
        const randomHint = hints[Math.floor(Math.random() * hints.length)];
        console.log('%c Hint of the day: ' + randomHint, 'color: #888; font-style: italic;');
    </script>

    <!--
    DEBUG: Response headers show X-Content-Type-Options: nosniff
    DEBUG: Character set: UTF-8
    NOTE: Entity decoding occurs before filter application in some code paths
    -->
</body>
</html>
