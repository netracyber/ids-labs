#!/usr/bin/env python3
"""
Direct update script to set static flags in all labs.
This directly replaces flag generation with static flag values.
"""

import re
from pathlib import Path

BASE_DIR = Path("/home/labuser/tools/lab-xss")

# Static flags for each lab
STATIC_FLAGS = {
    "search_query_xss_lab": {
        "flag": "IDS{e9f8a3b2d1c4a6f7e8b9d3c5a1f2e4d6}",
        "files": ["index.php"],
        "session_var": "_SESSION['flag']",
        "flag_var": "$flag"
    },
    "attribute_xss_lab": {
        "flag": "IDS{a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "js_string_context_lab": {
        "flag": "IDS{f1e2d3c4b5a6978f6e5d4c3b2a10987}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "document_write_lab": {
        "flag": "IDS{c4d3e2f1a6b7c8d9e0f1a2b3c4d5e6f}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "innerhtml_lab": {
        "flag": "IDS{9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "dom_innerhtml_xss_lab": {
        "flag": "IDS{e0b37cb9c327bc8a741bf11e6cd88025}",
        "files": ["dom_innerhtml_xss.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "formaction_xss_lab": {
        "flag": "IDS{1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d}",
        "files": ["index.php"],
        "session_var": "$_COOKIE['xss_flag'] or setcookie",
        "flag_var": "$flag"
    },
    "hash_innerhtml_xss_lab": {
        "flag": "IDS{ad46555c4b5829ca3c88b1bfaa171082}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "stored_xss_lab": {
        "flag": "IDS{1c8a5c15517d898e873a11dd32a19fa4}",
        "files": ["blog_post.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "stored_xss_href_lab": {
        "flag": "IDS{45f13c540e8997d935911c9987e167f6}",
        "files": ["view_post.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "lab-xss-event-handler": {
        "flag": "IDS{3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "lab-xss-js-string": {
        "flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}",
        "files": ["index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "lab-xss-medium": {
        "flag": "IDS{4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9}",
        "files": ["app/index.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "dom_xss_lab": {
        "flag": "IDS{6326ea06ab28fe9c08cd27189395a62e}",
        "files": ["dom_xss.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "js_string_xss_lab": {
        "flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}",
        "files": ["search.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
    "reflected_xss_lab": {
        "flag": "IDS{fdc13e38eb7c4bf9f157cab4a4304c}",
        "files": ["search.php"],
        "session_var": "$_SESSION['flag']",
        "flag_var": "$flag"
    },
}


def update_file_with_static_flag(file_path, static_flag):
    """Update a PHP file to use static flag directly"""
    if not file_path.exists():
        return False, f"File not found: {file_path}"

    content = file_path.read_text()
    original = content

    # Pattern 1: Replace FlagGenerator usage with static flag
    # Find: require_once 'FlagGenerator.php'; $flagGen = new FlagGenerator(); $flag = $flagGen->generate_flag();
    pattern1 = r"require_once\s+.*?FlagGenerator.*?\;\s*\$flagGen\s*=\s*new\s+FlagGenerator.*?\;\s*\$flag\s*=\s*\$flagGen->generate_flag\(\);"
    replacement1 = f'$flag = "{static_flag}";'
    content = re.sub(pattern1, replacement1, content, flags=re.DOTALL)

    # Pattern 2: Replace just the flag generation call
    pattern2 = r"\$flag\s*=\s*\$flagGen->generate_flag\(\);"
    replacement2 = f'$flag = "{static_flag}";'
    content = re.sub(pattern2, replacement2, content)

    # Pattern 3: Set static flag in session
    # Find: $_SESSION['flag'] = $flag;
    # Replace with: $_SESSION['flag'] = "IDS{...}";
    pattern3 = r"(\$_SESSION\['flag'\]\s*=\s*)\$flag;"
    replacement3 = f'\\1"{static_flag}";'
    content = re.sub(pattern3, replacement3, content)

    # Pattern 4: Replace cookie flag
    pattern4 = r"(setcookie\('xss_flag',\s*)\$flag"
    replacement4 = f"\\1'{static_flag}'"
    content = re.sub(pattern4, replacement4, content)

    # Pattern 5: Direct replacement of flag generator initialization
    pattern5 = r"\$flag\s*=\s*\"IDS\{[^}]+\"\;"
    replacement5 = f'$flag = "{static_flag}";'
    content = re.sub(pattern5, replacement5, content)

    # Pattern 6: Replace require_once FlagGenerator if no longer needed
    pattern6 = r"require_once\s+__DIR__\s*\.\s*['\"]\/FlagGenerator\.php['\"]\s*;\s*"
    # Keep it but make sure flag is static

    if content != original:
        file_path.write_text(content)
        return True, f"Updated {file_path.name}"

    return False, "No changes needed (already static)"


def main():
    print("=" * 70)
    print("DIRECT STATIC FLAG UPDATE FOR ALL LABS")
    print("=" * 70)

    success_count = 0
    for lab_name, lab_info in STATIC_FLAGS.items():
        lab_path = BASE_DIR / lab_name

        print(f"\n[{lab_name}]")
        print(f"  Flag: {lab_info['flag']}")

        for file_name in lab_info['files']:
            file_path = lab_path / file_name

            if file_path.exists():
                success, msg = update_file_with_static_flag(file_path, lab_info['flag'])
                if success:
                    print(f"  ✓ {msg}")
                    success_count += 1
                else:
                    print(f"  - {msg}")
            else:
                print(f"  ✗ File not found: {file_name}")

    print("\n" + "=" * 70)
    print(f"Updated {success_count} files")
    print("=" * 70)
    print("\n✅ Static flags set in all labs!")
    print("   Restart containers to apply changes:")
    print("   docker-compose down && docker-compose up -d")


if __name__ == "__main__":
    main()
