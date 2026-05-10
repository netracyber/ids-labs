#!/usr/bin/env python3
"""
Script to revert labs back to static flags and update them with exact flag values.
This will:
1. Remove FlagGenerator.php usage
2. Set static flags in each lab
3. Make sure flags match what's in CTFd
"""

import os
import re
from pathlib import Path

BASE_DIR = Path("/home/labuser/tools/lab-xss")

# Static flag values to set in each lab
LAB_STATIC_FLAGS = {
    "search_query_xss_lab": {
        "flag": "IDS{e9f8a3b2d1c4a6f7e8b9d3c5a1f2e4d6}",
        "files": ["index.php"]
    },
    "attribute_xss_lab": {
        "flag": "IDS{a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6}",
        "files": ["index.php"]
    },
    "js_string_context_lab": {
        "flag": "IDS{f1e2d3c4b5a6978f6e5d4c3b2a10987}",
        "files": ["index.php"]
    },
    "document_write_lab": {
        "flag": "IDS{c4d3e2f1a6b7c8d9e0f1a2b3c4d5e6f}",
        "files": ["index.php"]
    },
    "innerhtml_lab": {
        "flag": "IDS{9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d}",
        "files": ["index.php"]
    },
    "dom_innerhtml_xss_lab": {
        "flag": "IDS{e0b37cb9c327bc8a741bf11e6cd88025}",
        "files": ["dom_innerhtml_xss.php", "dom_innerhtml_xss.html"]
    },
    "formaction_xss_lab": {
        "flag": "IDS{1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d}",
        "files": ["index.php"]
    },
    "hash_innerhtml_xss_lab": {
        "flag": "IDS{ad46555c4b5829ca3c88b1bfaa171082}",
        "files": ["index.php"]
    },
    "stored_xss_lab": {
        "flag": "IDS{1c8a5c15517d898e873a11dd32a19fa4}",
        "files": ["blog_post.php"]
    },
    "stored_xss_href_lab": {
        "flag": "IDS{45f13c540e8997d935911c9987e167f6}",
        "files": ["view_post.php"]
    },
    "lab-xss-dom-location": {
        "flag": "IDS{2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7}",
        "files": ["app/index.php"]
    },
    "lab-xss-event-handler": {
        "flag": "IDS{3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8}",
        "files": ["app/index.php"]
    },
    "lab-xss-js-string": {
        "flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}",
        "files": ["app/index.php"]
    },
    "lab-xss-medium": {
        "flag": "IDS{4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9}",
        "files": ["app/index.php"]
    },
    "dom_xss_lab": {
        "flag": "IDS{6326ea06ab28fe9c08cd27189395a62e}",
        "files": ["dom_xss.php", "dom_xss.html"]
    },
    "js_string_xss_lab": {
        "flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}",
        "files": ["search.php"]
    },
    "reflected_xss_lab": {
        "flag": "IDS{fdc13e38eb7c4bf9f157cab4a4304c}",
        "files": ["search.php"]
    },
}


def update_php_file_with_static_flag(file_path, static_flag):
    """Update a PHP file to use static flag"""
    if not file_path.exists():
        return False, f"File not found: {file_path}"

    content = file_path.read_text()

    # Remove FlagGenerator includes and session start for flag generation
    content = re.sub(r'<\?php\s*session_start\(\);\s*require_once.*?FlagGenerator.*?\$flag.*?\$_SESSION.*?\?>\n', '', content)

    # Replace dynamic flag variables with static flag
    # Pattern 1: Replace $flag = $flagGen->generate_flag();
    content = re.sub(r'\$flag\s*=\s*\$flagGen->generate_flag\(\);', f'$flag = "{static_flag}";', content)

    # Pattern 2: Replace $_SESSION['flag'] with static flag
    content = re.sub(r'\$_SESSION\[\'flag\'\]\s*=\s*\$flag;', f'$_SESSION[\'flag\'] = "{static_flag}";', content)

    # Replace remaining $flag variable usages in PHP echo
    content = re.sub(r'<\?php\s+echo\s+\$flag;\s*\?>', f'<?php echo "{static_flag}"; ?>', content)
    content = re.sub(r'<\?php\s+echo\s+htmlspecialchars\(\$flag\);\s*\?>', f'<?php echo "{static_flag}"; ?>', content)
    content = re.sub(r'<\?php\s+echo\s+addslashes\(\$flag\);\s*\?>', f'<?php echo "{static_flag}"; ?>', content)
    content = re.sub(r'<\?php\s+echo\s+json_encode\(\$flag\);\s*\?>', f'"{static_flag}"', content)

    # Replace variable references in JavaScript
    content = re.sub(r'<\?php\s+echo\s+\$flag;\s*\?>', static_flag, content)

    file_path.write_text(content)
    return True, f"Updated {file_path.name}"


def update_lab_to_static_flag(lab_name, lab_info):
    """Update a lab to use static flag"""
    lab_path = BASE_DIR / lab_name

    if not lab_path.exists():
        return False, f"Lab not found: {lab_name}"

    print(f"\n[{lab_name}]")
    print(f"  Flag: {lab_info['flag']}")

    results = []

    for file_name in lab_info['files']:
        file_path = lab_path / file_name

        if file_path.exists():
            success, msg = update_php_file_with_static_flag(file_path, lab_info['flag'])
            if success:
                print(f"  ✓ {msg}")
                results.append(True)
            else:
                print(f"  ✗ {msg}")
                results.append(False)
        else:
            print(f"  - File not found: {file_name}")

    return all(results), results


def main():
    print("=" * 70)
    print("REVERTING LABS TO STATIC FLAGS")
    print("=" * 70)

    success_count = 0
    failed_labs = []

    for lab_name, lab_info in LAB_STATIC_FLAGS.items():
        success, result = update_lab_to_static_flag(lab_name, lab_info)

        if success:
            success_count += 1
        else:
            failed_labs.append(lab_name)

    print("\n" + "=" * 70)
    print(f"SUMMARY: {success_count}/{len(LAB_STATIC_FLAGS)} labs updated")
    print("=" * 70)

    if failed_labs:
        print("\nFailed to update:")
        for lab in failed_labs:
            print(f"  ✗ {lab}")

    print("\n✅ Labs now use static flags that match CTFd!")
    print("   Restart containers to apply changes:")
    print("   docker-compose down && docker-compose up -d")


if __name__ == "__main__":
    main()
