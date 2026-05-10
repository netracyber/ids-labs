<?php
/**
 * Reflected XSS Lab - JavaScript String Context
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
trackHit('xss-js-string-adv');
// ============ END TRACKING ============

// Static flag for this lab
$flag = "IDS{92798f74bc5cb240a73f2c9a8660c5ef}";

// Store flag in session for later validation
session_start();
$_SESSION['xss_flag'] = $flag;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevTools Pro | Developer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Application configuration
        const APP_CONFIG = {
            name: 'DevTools Pro',
            version: '2.5.0',
            mode: 'production',
            // User preferences system
            preferences: {
                theme: 'dark',
                fontSize: 14,
                // Available customization parameters
                params: ['username', 'locale', 'timezone', 'settings']
            }
        };

        console.log('%c' + APP_CONFIG.name + ' v' + APP_CONFIG.version, 'font-size: 14px; font-weight: bold; color: #00ff00;');

        // Security note
        console.log('Security: All inputs are properly escaped before JavaScript injection.');
        console.log('Available parameters: ' + APP_CONFIG.preferences.params.join(', '));

        // Random hint
        const hints = [
            "JavaScript strings can use single quotes ('), double quotes (\"), or backticks (`).",
            "To break out of a string context, you need to close the quote first.",
            "JavaScript statements end with semicolons (;).",
            "Comments (// or /* */) can help hide your payload's remaining code.",
            "Different injection contexts require different escape techniques."
        ];
        console.log('%c💡 Tip: ' + hints[Math.floor(Math.random() * hints.length)], 'color: #888; font-style: italic;');
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>⚡ DevTools Pro Dashboard</h1>
            <p class="tagline">Advanced developer utilities and tools</p>
        </header>

        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="profile.php">User Profile</a>
            <a href="settings.php">Settings</a>
            <a href="#" onclick="showHelp(); return false;">Help</a>
        </nav>

        <main>
            <section class="welcome">
                <h2>Welcome to DevTools Pro!</h2>
                <p>The ultimate toolkit for web developers and security researchers.</p>
            </section>

            <section class="features">
                <h3>Available Tools</h3>
                <div class="tool-grid">
                    <div class="tool-card">
                        <h4>👤 User Profile Generator</h4>
                        <p>Create dynamic user profiles with custom usernames and preferences.</p>
                        <a href="profile.php" class="btn">Open Profile</a>
                    </div>
                    <div class="tool-card">
                        <h4>⚙️ Settings Manager</h4>
                        <p>Configure application settings with URL parameters.</p>
                        <a href="settings.php" class="btn">Settings</a>
                    </div>
                    <div class="tool-card">
                        <h4>🔍 Code Inspector</h4>
                        <p>Analyze JavaScript code and injection points.</p>
                        <a href="#" onclick="alert('Coming soon!'); return false;" class="btn">Inspect</a>
                    </div>
                </div>
            </section>

            <section class="info-box">
                <h3>📖 How to Use</h3>
                <ol>
                    <li>Navigate to the <strong>User Profile</strong> section</li>
                    <li>Customize your profile using URL parameters</li>
                    <li>Your preferences will be reflected in the JavaScript context</li>
                </ol>
                <p class="note">
                    <strong>Pro Tip:</strong> Try accessing the profile with custom parameters like
                    <code>?username=YourName</code>
                </p>
            </section>

            <!-- Hidden clues -->
            <!--
            ========================================================================
            DEVTOOLS PRO - INTERNAL DOCUMENTATION
            ========================================================================

            Profile Generator System:
            ---------------------------
            The profile page reads user input from URL parameters and
            injects them into JavaScript variables for dynamic rendering.

            Parameter Processing:
            ----------------------
            - username: Injected into JS string literal for display
            - locale: Used for i18n settings
            - timezone: Applied to time calculations

            JavaScript Context:
            --------------------
            Variables are declared using const/let and assigned string values.
            The application uses various quote styles for different variables.

            Security Implementation:
            ------------------------
            - Quotes are escaped before injection (basic implementation)
            - HTML entities are NOT used (this is JavaScript, not HTML)
            - Backslash escaping is applied where needed

            Known Issues:
            --------------
            Some edge cases in quote escaping may exist for complex input.
            The escaping function uses basic string replacement methods.

            ========================================================================
            -->

            <section class="tips">
                <h3>💡 Development Tips</h3>
                <ul class="tips-list">
                    <li>Use the profile generator to test JavaScript injection scenarios</li>
                    <li>Different quote types require different escape sequences</li>
                    <li>Try mixing single and double quotes in your input</li>
                    <li>The browser's JS console can help debug your payloads</li>
                </ul>
            </section>

            <section class="security-info">
                <h3>🔒 Security Information</h3>
                <p>This application implements several security measures:</p>
                <ul>
                    <li>Input validation for all URL parameters</li>
                    <li>Proper escaping before JavaScript injection</li>
                    <li>Content Security Policy headers</li>
                    <li>Output encoding for different contexts</li>
                </ul>
                <p class="warning">
                    Note: This is a testing environment. Production applications
                    should use additional security layers.
                </p>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 DevTools Pro. All rights reserved.</p>
            <p class="version">Version 2.5.0 | JS Context Injection Test Environment</p>
        </footer>
    </div>

    <script>
        function showHelp() {
            alert('DevTools Pro Help:\n\n1. Visit profile.php to access user profile\n2. Use ?username=YourName to customize\n3. Other parameters: ?locale=en, ?timezone=UTC\n\nExample:\nprofile.php?username=Admin');
        }

        // Easter egg
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c JS Context Tip:', 'background: #222; color: #00ff00; padding: 3px;',
                'When injecting into JavaScript strings, you must escape the quote type used.');
            console.log('Single quote: \'');
            console.log('Double quote: "');
            console.log('Backtick: `');
        });

        // Random security tip
        const jsTips = [
            "Backslashes (\\) are used to escape characters in strings",
            "To include a quote in a string, use \\\" or \\'",
            "Template literals use backticks (`) and allow ${} expressions",
            "Comments (//) can hide the rest of a line in JavaScript",
            "You can chain multiple statements with semicolons"
        ];
        console.log('%c💡 JS Tip: ' + jsTips[Math.floor(Math.random() * jsTips.length)], 'color: #666;');
    </script>

    <!--
    ========================================================================
    DEBUG INFO - PROFILE SYSTEM
    ========================================================================

    Profile URL Examples:
    ---------------------
    profile.php?username=John
    profile.php?username=John&locale=en
    profile.php?settings={"theme":"dark"}

    JavaScript Injection Points:
    ----------------------------
    - Username variable: var username = '[USER_INPUT]';
    - Settings object: var settings = { user: '[USER_INPUT]' };

    Escaping Implementation:
    -----------------------
    function escapeForJS(input) {
        input = input.replace(/\\/g, '\\\\');  // Backslash first
        input = input.replace(/'/g, "\\'");    // Single quotes
        input = input.replace(/"/g, '\\"');    // Double quotes
        return input;
    }

    Challenge:
    ----------
    Can you find a way to bypass this escaping and execute arbitrary code?
    Hint: The order of escaping operations matters...

    ========================================================================
    -->
</body>
</html>
