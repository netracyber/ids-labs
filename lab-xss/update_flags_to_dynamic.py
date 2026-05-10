#!/usr/bin/env python3
"""
Script to update all static flag labs to use dynamic flag generation.
This will:
1. Add FlagGenerator.php to labs that don't have it
2. Add generate_flag.py to labs that don't have it
3. Convert static HTML files to PHP with dynamic flag generation
4. Update docker-compose.yml for proper flag generation
"""

import os
import shutil
from pathlib import Path

# Base directory
BASE_DIR = Path("/home/labuser/tools/lab-xss")

# Labs with static flags that need updating
STATIC_FLAG_LABS = {
    "dom_innerhtml_xss_lab": {
        "current_flag": "IDS{e0b37cb9c327bc8a741bf11e6cd88025}",
        "main_file": "dom_innerhtml_xss.html",
        "type": "html"
    },
    "dom_xss_lab": {
        "current_flag": "IDS{6326ea06ab28fe9c08cd27189395a62e}",
        "main_file": "dom_xss.html",
        "type": "html"
    },
    "js_string_xss_lab": {
        "current_flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}",
        "main_file": "index.html",  # Need to verify
        "type": "html"
    },
    "reflected_xss_lab": {
        "current_flag": "IDS{fdc13e38eb7c4bf9f157cab4a4304c}",
        "main_file": None,  # Need to check
        "type": "unknown"
    },
    "stored_xss_lab": {
        "current_flag": "IDS{1c8a5c15517d898e873a11dd32a19fa4}",
        "main_file": "blog_post.php",
        "type": "php"
    },
    "stored_xss_href_lab": {
        "current_flag": "IDS{45f13c540e8997d935911c9987e167f6}",
        "main_file": "view_post.php",
        "type": "php"
    },
    "formaction_xss_lab": {
        "current_flag": "FLAG{formaction_xss_master_[random]}",
        "main_file": "index.php",
        "type": "php"
    },
}

# FlagGenerator.php template
FLAG_GENERATOR_PHP = '''<?php
/**
 * Flag Generator Class for XSS Labs
 * Integrates with the Python flag generator
 */

class FlagGenerator {
    private $flagFile;
    private $pythonScript;

    public function __construct($flagFile = '/tmp/current_flag.txt', $pythonScript = null) {
        $this->flagFile = $flagFile;
        // Use local tools directory if available, otherwise use app tools
        $localScript = __DIR__ . '/tools/generate_flag.py';
        $this->pythonScript = $pythonScript ?? (file_exists($localScript) ? $localScript : '/usr/local/bin/generate_flag.py');
    }

    /**
     * Generate or retrieve existing flag
     */
    public function generate_flag() {
        // Check if flag already exists
        if (file_exists($this->flagFile)) {
            $flag = trim(file_get_contents($this->flagFile));
            if (!empty($flag) && strpos($flag, 'IDS{') === 0) {
                return $flag;
            }
        }

        // Generate new flag using Python script
        $flag = $this->run_python_generator();

        // Save to file
        file_put_contents($this->flagFile, $flag);

        return $flag;
    }

    /**
     * Run Python flag generator script
     */
    private function run_python_generator() {
        $output = shell_exec("python3 {$this->pythonScript} 2>&1");

        // Extract flag from output
        if (preg_match('/IDS\\{[A-Za-z0-9]+\\}/', $output, $matches)) {
            return $matches[0];
        }

        // Fallback: generate flag directly in PHP
        return $this->generate_fallback_flag();
    }

    /**
     * Fallback flag generation if Python script fails
     */
    private function generate_fallback_flag() {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random_code = '';
        for ($i = 0; $i < 20; $i++) {
            $random_code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return 'IDS{' . $random_code . '}';
    }
}
'''

# generate_flag.py template
GENERATE_FLAG_PY = '''import secrets
import string
import os

def generate_random_flag():
    """Generate a random flag in the format IDS{code_acak...}"""
    # Generate a random string of 32 characters (hexadecimal)
    random_code = secrets.token_hex(16)
    flag = f"IDS{{{random_code}}}"
    return flag

def generate_random_flag_alphanumeric():
    """Generate a random flag with alphanumeric characters in the format IDS{code_acak...}"""
    # Generate a random string of 16-32 alphanumeric characters
    length = 20  # Fixed length for consistency
    characters = string.ascii_letters + string.digits
    random_code = ''.join(secrets.choice(characters) for _ in range(length))
    flag = f"IDS{{{random_code}}}"
    return flag

def save_flag_to_file(flag, filename="current_flag.txt"):
    """Save the generated flag to a file"""
    with open(filename, 'w') as f:
        f.write(flag)
    print(f"Flag saved to {filename}: {flag}")
    return flag

def get_existing_flag_or_generate(filename="current_flag.txt"):
    """Get existing flag from file or generate a new one if file doesn't exist"""
    if os.path.exists(filename):
        with open(filename, 'r') as f:
            flag = f.read().strip()
        print(f"Existing flag found: {flag}")
        return flag
    else:
        flag = generate_random_flag()
        save_flag_to_file(flag, filename)
        return flag

if __name__ == "__main__":
    print("Flag Generator for XSS Lab")
    print("=" * 40)

    # Generate a new random flag
    new_flag = generate_random_flag()
    print(f"Generated flag: {new_flag}")

    # Save to file
    save_flag_to_file(new_flag)

    # Also demonstrate getting existing flag or generating new one
    print("\\nUsing get_existing_flag_or_generate function:")
    flag = get_existing_flag_or_generate()
    print(f"Result flag: {flag}")
'''


def setup_tools_directory(lab_path):
    """Create tools directory and add generate_flag.py"""
    tools_dir = lab_path / "tools"
    tools_dir.mkdir(exist_ok=True)

    flag_script = tools_dir / "generate_flag.py"
    if not flag_script.exists():
        flag_script.write_text(GENERATE_FLAG_PY)
        print(f"  ✓ Created: {flag_script}")
    else:
        print(f"  - Already exists: {flag_script}")

    return tools_dir


def add_flag_generator_to_php_lab(lab_path, lab_name):
    """Add FlagGenerator.php to a PHP-based lab"""
    flag_gen_file = lab_path / "FlagGenerator.php"

    if not flag_gen_file.exists():
        flag_gen_file.write_text(FLAG_GENERATOR_PHP)
        print(f"  ✓ Created: {flag_gen_file}")
    else:
        print(f"  - Already exists: {flag_gen_file}")


def convert_html_to_php_with_flag(html_file, lab_name):
    """Convert an HTML file to PHP with dynamic flag generation"""
    if not html_file.exists():
        print(f"  ✗ File not found: {html_file}")
        return None

    # Read the HTML content
    html_content = html_file.read_text()

    # Find the static flag and replace with dynamic flag generation
    import re

    # Add PHP session start and flag generation at the beginning
    php_header = '''<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
$_SESSION['flag'] = $flag;
?>
'''

    # Replace static flags in JavaScript with PHP echo
    # Pattern 1: alert('Flag: IDS{...}')
    def replace_static_flag(match):
        return "alert('Flag: ' + <?php echo json_encode($flag); ?>);"

    # Replace various flag patterns
    patterns = [
        (r"alert\(['\"]Congratulations! Flag: IDS\{[a-zA-Z0-9]+\}['\"]\)", "alert('Congratulations! Flag: ' + <?php echo json_encode($flag); ?>);"),
        (r"Flag: <strong>IDS\{[a-zA-Z0-9]+\}</strong>", "Flag: <strong><?php echo htmlspecialchars($flag); ?></strong>"),
        (r"window\.showFlag\s*=\s*function\(\)\s*\{\s*alert\(['\"]Congratulations! Flag: IDS\{[a-zA-Z0-9]+\}['\"]\)", f"window.showFlag = function() {{ alert('Congratulations! Flag: <?php echo $flag; ?>'); }}"),
        (r"IDS\{[a-zA-Z0-9]+\}", "<?php echo $flag; ?>"),
    ]

    modified_content = html_content
    for pattern, replacement in patterns:
        modified_content = re.sub(pattern, replacement, modified_content)

    # Create new PHP file
    php_file = html_file.with_suffix('.php')
    php_file.write_text(php_header + modified_content)

    print(f"  ✓ Converted: {html_file.name} -> {php_file.name}")
    return php_file


def update_php_file_with_dynamic_flag(php_file, lab_name):
    """Update an existing PHP file to use dynamic flag generation"""
    if not php_file.exists():
        print(f"  ✗ File not found: {php_file}")
        return

    content = php_file.read_text()

    # Check if it already has FlagGenerator
    if 'FlagGenerator' in content or 'require_once' in content and 'FlagGenerator.php' in content:
        print(f"  - Already uses FlagGenerator: {php_file.name}")
        return

    # Find the static flag
    import re

    # Add session start and flag generator at the beginning after <?php
    php_init = '''<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
$_SESSION['flag'] = $flag;
'''

    # Replace static flags
    patterns = [
        (r"IDS\{[a-zA-Z0-9]+\}", "<?php echo $flag; ?>"),
        (r"FLAG\{formaction_xss_master_\[random\]\}", "<?php echo 'FLAG{formaction_xss_master_' . bin2hex(random_bytes(8)) . '}'; ?>"),
        (r"'FLAG\{formaction_xss_master_'\s*\.\s*bin2hex\(random_bytes\(8\)\)\s*\.\s*'}'", "<?php echo $flag; ?>"),
    ]

    modified_content = content

    # Add flag generation if not present
    if '<?php' in modified_content and 'FlagGenerator' not in modified_content:
        # Replace first <?php with full initialization
        modified_content = modified_content.replace('<?php', php_init, 1)

    for pattern, replacement in patterns:
        modified_content = re.sub(pattern, replacement, modified_content)

    php_file.write_text(modified_content)
    print(f"  ✓ Updated: {php_file.name}")


def main():
    """Main function to update all labs"""
    print("=" * 70)
    print("Updating XSS Labs to Use Dynamic Flag Generation")
    print("=" * 70)

    for lab_name, lab_info in STATIC_FLAG_LABS.items():
        lab_path = BASE_DIR / lab_name

        if not lab_path.exists():
            print(f"\n✗ Lab not found: {lab_name}")
            continue

        print(f"\n[{lab_name}]")
        print(f"  Current flag: {lab_info['current_flag']}")
        print(f"  Type: {lab_info['type']}")

        # Setup tools directory
        setup_tools_directory(lab_path)

        # Add FlagGenerator.php
        add_flag_generator_to_php_lab(lab_path, lab_name)

        # Update main file based on type
        if lab_info['type'] == 'html' and lab_info['main_file']:
            html_file = lab_path / lab_info['main_file']
            convert_html_to_php_with_flag(html_file, lab_name)
        elif lab_info['type'] == 'php' and lab_info['main_file']:
            php_file = lab_path / lab_info['main_file']
            update_php_file_with_dynamic_flag(php_file, lab_name)

    print("\n" + "=" * 70)
    print("Update complete!")
    print("=" * 70)
    print("\nNext steps:")
    print("1. Update docker-compose.yml to mount generate_flag.py")
    print("2. Restart the labs to generate new dynamic flags")
    print("3. Test each lab to verify flag generation works")


if __name__ == "__main__":
    main()
