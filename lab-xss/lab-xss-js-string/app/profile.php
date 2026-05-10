<?php
/**
 * Reflected XSS Lab - JavaScript String Context
 * Vulnerable Profile Page
 */

session_start();

// Get flag from session
$flag = isset($_SESSION['xss_flag']) ? $_SESSION['xss_flag'] : 'IDS{session_error}';

// Get user input from various parameters
$username = isset($_GET['username']) ? $_GET['username'] : 'Guest';
$locale = isset($_GET['locale']) ? $_GET['locale'] : 'en';
$timezone = isset($_GET['timezone']) ? $_GET['timezone'] : 'UTC';
$settings = isset($_GET['settings']) ? $_GET['settings'] : '{}';

/**
 * Escaping function for JavaScript string context
 * Has vulnerabilities that can be exploited
 */
function escapeForJS($input) {
    // Order matters! This implementation has a subtle flaw
    $input = str_replace('\\', '\\\\', $input);  // Escape backslashes first
    $input = str_replace("'", "\\'", $input);    // Escape single quotes
    $input = str_replace('"', '\\"', $input);    // Escape double quotes
    return $input;
}

// Apply escaping to user inputs
$safeUsername = escapeForJS($username);
$safeLocale = escapeForJS($locale);
$safeTimezone = escapeForJS($timezone);
$safeSettings = escapeForJS($settings);

// For the settings page - different context (backtick strings)
// Less escaping applied here (intentionally vulnerable)
$rawSettings = $settings; // No escaping - the vulnerability!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | DevTools Pro</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Flag storage - only accessible through successful XSS
        const USER_FLAG = "<?php echo $flag; ?>";

        // ============================================================================
        // VULNERABLE CODE: User input reflected into JavaScript string context
        // ============================================================================
        // The following variables are populated from URL parameters
        // with basic escaping that has exploitable weaknesses
        // ============================================================================

        // Username variable - uses single quotes
        const userProfile = {
            username: '<?php echo $safeUsername; ?>',
            locale: '<?php echo $safeLocale; ?>',
            timezone: '<?php echo $safeTimezone; ?>',
            displayDate: new Date().toLocaleString('<?php echo $safeLocale; ?>')
        };

        // Settings object - VULNERABLE: uses backticks with minimal escaping
        const userSettings = {
            // This is vulnerable - backtick strings with raw user input
            custom: `<?php echo $rawSettings; ?>`,
            // Another vulnerable point - direct injection in object value
            preferences: {
                theme: 'default',
                // VULNERABLE: mixing quote styles can lead to issues
                customValue: "<?php echo isset($_GET['custom']) ? htmlspecialchars($_GET['custom'], ENT_QUOTES) : 'none'; ?>"
            }
        };

        // Debug: Log profile info
        console.log('User profile loaded:', userProfile);
        console.log('Settings loaded:', userSettings);

        // Display username in page (after page loads)
        function displayProfile() {
            const displayEl = document.getElementById('username-display');
            if (displayEl) {
                displayEl.textContent = userProfile.username;
            }
        }

        // Validate XSS and show flag
        function validateExploit() {
            alert('Congratulations! XSS Flag: ' + USER_FLAG);
            return USER_FLAG;
        }

        // Application initialization
        console.log('%cProfile system initialized', 'color: green; font-weight: bold;');
        console.log('Available variables for testing:');
        console.log('  - userProfile.username');
        console.log('  - userProfile.locale');
        console.log('  - userSettings.custom');
        console.log('  - userSettings.preferences.customValue');
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>👤 User Profile</h1>
            <p class="tagline">Manage your developer profile</p>
        </header>

        <nav>
            <a href="index.php">Home</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="settings.php">Settings</a>
            <a href="#" onclick="showHelp(); return false;">Help</a>
        </nav>

        <main>
            <section class="profile-header">
                <h2>Your Profile</h2>
                <div class="profile-info">
                    <p><strong>Username:</strong> <span id="username-display"></span></p>
                    <p><strong>Locale:</strong> <?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Timezone:</strong> <?php echo htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </section>

            <section class="customization">
                <h3>🔧 Customize Your Profile</h3>
                <p>Use URL parameters to customize your profile settings:</p>

                <div class="params-list">
                    <div class="param-item">
                        <code>?username=YourName</code>
                        <span>Set your display username</span>
                    </div>
                    <div class="param-item">
                        <code>?locale=en</code>
                        <span>Set your locale/language</span>
                    </div>
                    <div class="param-item">
                        <code>?timezone=UTC</code>
                        <span>Set your timezone</span>
                    </div>
                    <div class="param-item">
                        <code>?settings=custom_data</code>
                        <span>Custom settings (advanced)</span>
                    </div>
                    <div class="param-item">
                        <code>?custom=value</code>
                        <span>Custom preference value</span>
                    </div>
                </div>

                <div class="example-box">
                    <p><strong>Try this:</strong></p>
                    <a href="profile.php?username=Developer" class="example-link">profile.php?username=Developer</a>
                    <a href="profile.php?settings=test" class="example-link">profile.php?settings=test</a>
                </div>
            </section>

            <!-- Hidden clues -->
            <!--
            ========================================================================
            PROFILE SYSTEM - JAVASCRIPT INJECTION ANALYSIS
            ========================================================================

            Vulnerable Code Locations:
            ---------------------------
            1. Line ~65 (userProfile.username) - Single quote context
               Code: username: '<?php echo $safeUsername; ?>'
               Escaping: escapeForJS() function

            2. Line ~75 (userSettings.custom) - Backtick context
               Code: custom: `<?php echo $rawSettings; ?>`
               Escaping: NONE - direct injection!

            3. Line ~80 (userSettings.preferences.customValue) - Double quote context
               Code: customValue: "<?php echo ... ?>"
               Escaping: htmlspecialchars() (HTML context, wrong for JS!)

            Escaping Function Analysis:
            ---------------------------
            function escapeForJS($input) {
                $input = str_replace('\\', '\\\\', $input);  // Step 1
                $input = str_replace("'", "\\'", $input);    // Step 2
                $input = str_replace('"', '\\"', $input);    // Step 3
                return $input;
            }

            Bypass Opportunities:
            ---------------------
            1. Backtick context (settings parameter) - NO ESCAPING AT ALL!
              Payload: `; alert(1); //

            2. The escapeForJS order issue:
              - If you input \\'  it becomes \\' then \\' then \\'
              - Can we craft input to break out?

            3. htmlspecialchars() in JS context is WRONG:
              - HTML entities are NOT valid JavaScript escapes
              - &quot; is not " in JavaScript
              - This creates syntax errors or opportunities

            Test Cases to Try:
            -------------------
            ?settings=`; alert(1); //
            ?settings=${alert(1)}
            ?custom=</script><script>alert(1)</script>
            ?username='; alert(1); //

            ========================================================================
            -->

            <section class="debug-section">
                <h3>🔍 Debug Information</h3>
                <p>For testing and development:</p>
                <ul>
                    <li>Open Browser DevTools Console to see variable values</li>
                    <li>Check the Sources tab to view injected JavaScript code</li>
                    <li>Use the Network tab to inspect HTTP requests</li>
                </ul>
                <p class="note">
                    <strong>Note:</strong> This profile system uses various quote styles
                    for different variables. Some may be more secure than others.
                </p>
            </section>

            <section class="tips">
                <h3>💡 Tips for Testing</h3>
                <ul class="tips-list">
                    <li>Try different quote styles: single ('), double ("), backtick (`)</li>
                    <li>The backslash (\) is used to escape characters in JavaScript strings</li>
                    <li>Template literals (backticks) allow ${expression} syntax</li>
                    <li>JavaScript statements can be chained with semicolons</li>
                    <li>Comments (// or /* */) can hide remaining code</li>
                </ul>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 DevTools Pro. All rights reserved.</p>
            <p class="version">Profile System v1.2.0 | Context: JavaScript String Injection</p>
        </footer>
    </div>

    <script>
        // Initialize profile display
        document.addEventListener('DOMContentLoaded', function() {
            displayProfile();

            console.log('%c=== JAVASCRIPT CONTEXT INJECTION LAB ===', 'font-weight: bold; color: #00ff00;');
            console.log('');
            console.log('Examine the following code in the Sources panel:');
            console.log('1. userProfile object - single quote context');
            console.log('2. userSettings object - backtick and double quote contexts');
            console.log('');
            console.log('Escaping function used:');
            console.log('  - escapeForJS() for username, locale, timezone');
            console.log('  - NO escaping for settings (backtick context)');
            console.log('  - htmlspecialchars() for custom (WRONG for JS!)');
            console.log('');
            console.log('%cHint: Backtick strings have minimal escaping!', 'color: yellow; font-weight: bold;');

            // Random injection tip
            const tips = [
                "Template literals using backticks allow ${expression} for interpolation",
                "To break out: close the quote, add code, terminate the statement",
                "Use // comments to hide any remaining JavaScript code",
                "Backticks are newer syntax - escaping may be inconsistent",
                "Mixing quote styles can cause escaping logic errors"
            ];
            console.log('%c💡 ' + tips[Math.floor(Math.random() * tips.length)], 'color: #888; font-style: italic;');
        });

        function showHelp() {
            alert('Profile Help:\n\nCustomize your profile with URL parameters:\n\n- ?username=YourName\n- ?locale=en\n- ?settings=custom\n\nExample:\nprofile.php?username=Admin&settings=test');
        }

        // Payload testing helper (for authorized testing)
        function testPayload(payload) {
            console.log('Testing payload:', payload);
            console.log('Length:', payload.length);
            console.log('First char:', payload.charAt(0));
            console.log('Last char:', payload.charAt(payload.length - 1));
        }
    </script>

    <!--
    ========================================================================
    SOLUTION HINTS (Try these payloads)
    ========================================================================

    EASIEST - Backtick context (settings parameter):
    -------------------------------------------------
    profile.php?settings=`; validateExploit(); //
    Result: custom: `; validateExploit(); //`
    Breaks out, calls function, comments rest

    ALTERNATIVE - Template expression:
    ----------------------------------
    profile.php?settings=${validateExploit()}
    Result: custom: `${validateExploit()}`
    Template literal executes the function

    ADVANCED - Escaping bypass (username parameter):
    ------------------------------------------------
    profile.php?username=\'; validateExploit(); //
    Input: \'; validateExploit(); //
    After \\ becomes \\\\: \\'; validateExploit(); //
    After \' becomes \\': \\\'; validateExploit(); //
    After \" becomes \\": \\\'; validateExploit(); //
    Result: username = '\\\'; validateExploit(); //'
    Still escapes - but what if input is \\' ?

    CHALLENGE: Can you craft input to break the escapeForJS function?
    Hint: The order of replacements matters...

    ========================================================================
    -->
</body>
</html>
