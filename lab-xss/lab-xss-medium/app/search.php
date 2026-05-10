<?php
/**
 * Reflected XSS Lab - Medium Difficulty
 * Vulnerable Search Page with Bypassable Filter
 */

session_start();

// Get flag from session
$flag = isset($_SESSION['xss_flag']) ? $_SESSION['xss_flag'] : 'IDS{session_error}';

// Get search query from multiple possible parameters
$query = '';
if (isset($_GET['query'])) {
    $query = $_GET['query'];
} elseif (isset($_GET['q'])) {
    // Legacy parameter - less validation (the vulnerability is here!)
    $query = $_GET['q'];
} elseif (isset($_GET['search'])) {
    $query = $_GET['search'];
}

// XSS Filter function - has bypassable vulnerabilities
function xss_filter($input) {
    // Step 1: Remove script tags (case-insensitive)
    $input = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $input);

    // Step 2: Remove javascript: protocol (case-insensitive)
    $input = preg_replace('/javascript\s*:/is', '', $input);

    // Step 3: Remove common dangerous event handlers
    $dangerous_events = ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus', 'onblur'];
    foreach ($dangerous_events as $event) {
        // Case-insensitive removal
        $input = preg_replace('/' . $event . '\s*=/is', '', $input);
    }

    // Step 4: Remove some dangerous tags
    $dangerous_tags = ['<iframe', '<object', '<embed', '<link'];
    foreach ($dangerous_tags as $tag) {
        $input = str_ireplace($tag, '', $input);
    }

    return $input;
}

// Apply filtering to main parameter
$filtered_query = xss_filter($query);

// Check if payload executed (flag will be sent from client-side)
$exploit_successful = false;

// Detect XSS attempt patterns for validation
$xss_patterns = [
    '/<script/i',
    '/javascript:/i',
    '/onerror/i',
    '/onload/i',
    '/onclick/i',
    '/on\w+\s*=/i',
    '/<svg/i',
    '/<img/i',
    '/<iframe/i',
    '/<object/i',
    '/<embed/i'
];

$pattern_detected = false;
foreach ($xss_patterns as $pattern) {
    if (preg_match($pattern, $query)) {
        $pattern_detected = true;
        break;
    }
}

// For the 'q' parameter, we apply a different (weaker) filter
$legacy_filter = function($input) {
    // Legacy filter - only blocks exact case-sensitive matches
    $input = str_replace(['<script>', '</script>', 'javascript:'], '', $input);
    // Very basic event handler removal - only exact matches
    $input = str_replace('onerror=', '', $input);
    $input = str_replace('onload=', '', $input);

    return $input;
};

// For the vulnerable 'q' parameter, use weak filter
$legacy_display = isset($_GET['q']) ? $legacy_filter($_GET['q']) : $filtered_query;

// Main parameter uses strong filter
$main_display = $filtered_query;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | SecureShop</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Flag stored in memory - only accessible through successful XSS
        const SECRET_FLAG = "<?php echo $flag; ?>";

        // Validation endpoint for XSS completion
        function validateExploit() {
            // This function is called when XSS is successful
            alert('Congratulations! XSS Flag: ' + SECRET_FLAG);
            return SECRET_FLAG;
        }

        // Debug: Parameter info
        const PARAM_INFO = {
            current: "<?php echo isset($_GET['q']) ? 'q' : 'query'; ?>",
            available: ['query', 'q', 'search'],
            note: "Legacy parameters may have different validation rules"
        };

        console.log("Search page loaded. Parameter: " + PARAM_INFO.current);
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔍 Search Results</h1>
        </header>

        <nav>
            <a href="index.php">Home</a>
            <a href="search.php" class="active">Advanced Search</a>
            <a href="#" onclick="showHelp(); return false;">Help</a>
        </nav>

        <main>
            <section class="search-section">
                <form action="search.php" method="GET" class="search-form-inline">
                    <div class="input-group">
                        <label>Search:</label>
                        <input type="text" name="query" placeholder="Enter search term..." value="<?php echo htmlspecialchars($main_display, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn-search">🔍</button>
                    </div>
                </form>
            </section>

            <section class="results-section">
                <h2>Results for: <span id="search-term"><?php echo $main_display; ?></span></h2>

                <?php if (empty($query)): ?>
                    <div class="no-results">
                        <p>Please enter a search term to find products.</p>
                    </div>
                <?php else: ?>
                    <div class="results-count">
                        <p>Found 0 results for "<strong><?php echo $main_display; ?></strong>"</p>
                        <p class="try-again">Try different keywords or check your spelling.</p>
                    </div>

                    <div class="products-preview">
                        <h3>Suggested Products</h3>
                        <div class="product-list">
                            <div class="product-item">
                                <span class="product-name">Premium Widget</span>
                                <span class="product-price">$29.99</span>
                            </div>
                            <div class="product-item">
                                <span class="product-name">Standard Gadget</span>
                                <span class="product-price">$19.99</span>
                            </div>
                            <div class="product-item">
                                <span class="product-name">Basic Tool</span>
                                <span class="product-price">$9.99</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Hidden reflection point for legacy parameter - the main vulnerability -->
            <?php if (isset($_GET['q'])): ?>
            <div class="legacy-debug" style="display:none;" data-query="<?php echo $legacy_display; ?>">
                <!-- Legacy parameter reflection - minimally filtered -->
                <span><?php echo $legacy_display; ?></span>
            </div>
            <?php endif; ?>

            <!-- Another reflection point in attribute context -->
            <div class="search-meta" data-search-term="<?php echo htmlspecialchars($main_display, ENT_QUOTES, 'UTF-8'); ?>">
                <p>Search performed at: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>

            <!-- Hidden clues -->
            <!--
            ================================================================
            SEARCH SYSTEM v2.4.1 - INTERNAL DOCUMENTATION
            ================================================================

            Parameter Processing:
            ---------------------
            1. 'query' -> xss_filter() - Full validation (strict)
            2. 'q' -> legacy_filter() - Minimal validation (backward compat)
            3. 'search' -> xss_filter() - Full validation (strict)

            Legacy Filter Notes:
            --------------------
            - Only removes EXACT case-sensitive matches
            - Does NOT use regex for pattern matching
            - Applied BEFORE HTML entity encoding
            - Designed for old API compatibility

            Bypass Possibilities:
            ---------------------
            - Case variations: OnError, ONERROR, OnError
            - Alternative tags: svg, details, marquee, body
            - Alternative events: ontoggle, onbegin, onend, onrepeat
            - Encoding tricks: HTML entities, URL encoding, Unicode

            Remember: The filter is applied server-side BEFORE the data
            is sent to the browser. What reaches the browser is what matters.

            ================================================================
            -->

            <section class="advanced-options">
                <h3>🔧 Advanced Options</h3>
                <p class="small">Power users can try alternative search parameters:</p>
                <ul class="small">
                    <li><code>?q=your_search</code> - Legacy quick search (minimal filtering)</li>
                    <li><code>?query=your_search</code> - Standard search (full filtering)</li>
                    <li><code>?search=your_search</code> - Alias for query</li>
                </ul>
                <p class="warning">⚠️ Note: Different parameters may have different validation rules!</p>
            </section>

        </main>

        <footer>
            <p>&copy; 2024 SecureShop. All rights reserved.</p>
            <p class="small-text">Filter version: 2.4.1 | Legacy mode: available via 'q' parameter</p>
        </footer>
    </div>

    <script>
        function showHelp() {
            alert('Search Help:\n\nTry different parameters:\n- ?query=term (standard)\n- ?q=term (legacy)\n\nLegacy mode may have different behavior.');
        }

        // Console hints for curious users
        console.log('%c Parameter Info:', 'font-weight: bold; color: #0066cc;');
        console.log(PARAM_INFO);

        <?php if ($pattern_detected): ?>
        console.log('%c⚠️ XSS pattern detected in input!', 'color: orange; font-weight: bold;');
        console.log('The filter may have modified your input. Try alternative approaches.');
        <?php endif; ?>

        // Reflection test - helps users understand how input is reflected
        const searchTerm = document.getElementById('search-term');
        if (searchTerm) {
            console.log('Your input was reflected as:', searchTerm.innerHTML);
            console.log('HTML entities:', searchTerm.innerHTML === searchTerm.textContent ? 'None found' : 'Present');
        }

        // Hint about bypass techniques
        console.log('%c Tip:', 'color: #0066cc;', 'Some filters are case-sensitive. Try different cases or alternative event handlers.');
    </script>

    <!--
    ================================================================
    FILTER BEHAVIOR DEBUG:
    ================================================================

    Parameter: q (legacy)
    Filter type: String replacement (case-sensitive)
    Blocked patterns (exact match only):
      - <script> (not <Script>, <SCRIPT>, etc.)
      - javascript: (not JavaScript:, JAVASCRIPT:, etc.)
      - onerror= (not OnError=, ONERROR=, etc.)

    Allowed patterns (examples):
      - <svg onload=alert(1)>
      - <img src=x onerror=alert(1)> (case variation)
      - <details open ontoggle=alert(1)>

    ================================================================
    -->
</body>
</html>
