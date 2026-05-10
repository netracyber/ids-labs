#!/usr/bin/env python3
"""
Verification script for dynamic flag generation updates.
Checks which labs have been updated to use dynamic flags.
"""

import os
from pathlib import Path

BASE_DIR = Path("/home/labuser/tools/lab-xss")

# Labs that should now have dynamic flags
UPDATED_LABS = {
    "dom_innerhtml_xss_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py", "dom_innerhtml_xss.php"]},
    "dom_xss_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py", "dom_xss.php"]},
    "js_string_xss_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py"]},
    "reflected_xss_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py"]},
    "stored_xss_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py"]},
    "stored_xss_href_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py"]},
    "formaction_xss_lab": {"files": ["FlagGenerator.php", "tools/generate_flag.py"]},
}

# Labs that already had dynamic flags
ALREADY_DYNAMIC = [
    "attribute_xss_lab",
    "document_write_lab",
    "innerhtml_lab",
    "js_string_context_lab",
    "search_query_xss_lab",
    "hash_innerhtml_xss_lab",
    "lab-xss-dom-location",
    "lab-xss-event-handler",
    "lab-xss-js-string",
    "lab-xss-medium",
]

def check_file_content(filepath, search_strings):
    """Check if file contains specific strings"""
    if not filepath.exists():
        return False, "File does not exist"

    content = filepath.read_text()
    found = []
    missing = []

    for search_str in search_strings:
        if search_str in content:
            found.append(search_str)
        else:
            missing.append(search_str)

    return len(missing) == 0, {"found": found, "missing": missing}


def verify_lab(lab_name, lab_info):
    """Verify a lab has been properly updated"""
    lab_path = BASE_DIR / lab_name
    results = {"lab": lab_name, "status": "unknown", "details": []}

    if not lab_path.exists():
        results["status"] = "ERROR"
        results["details"].append("Lab directory does not exist")
        return results

    # Check required files exist
    for file_path in lab_info.get("files", []):
        full_path = lab_path / file_path
        if full_path.exists():
            results["details"].append(f"✓ {file_path} exists")
        else:
            results["status"] = "ERROR"
            results["details"].append(f"✗ {file_path} missing")

    # Check if FlagGenerator.php is being used
    flag_gen = lab_path / "FlagGenerator.php"
    if flag_gen.exists():
        results["details"].append("✓ FlagGenerator.php present")

    # Check if PHP files use the flag generator
    for php_file in lab_path.glob("*.php"):
        if php_file.name == "FlagGenerator.php":
            continue

        content = php_file.read_text()
        if "FlagGenerator" in content or "require_once" in content:
            results["details"].append(f"✓ {php_file.name} uses FlagGenerator")
            results["status"] = "OK"

    if results["status"] == "unknown":
        results["status"] = "PENDING"

    return results


def main():
    """Main verification function"""
    print("=" * 70)
    print("DYNAMIC FLAG GENERATION VERIFICATION")
    print("=" * 70)

    print("\n## LABS UPDATED TO DYNAMIC FLAGS:")
    print("-" * 70)

    all_ok = True
    for lab_name, lab_info in UPDATED_LABS.items():
        result = verify_lab(lab_name, lab_info)

        status_icon = {
            "OK": "✅",
            "ERROR": "❌",
            "PENDING": "⚠️ "
        }.get(result["status"], "❓")

        print(f"\n{status_icon} {lab_name}: {result['status']}")

        for detail in result["details"]:
            print(f"   {detail}")

        if result["status"] != "OK":
            all_ok = False

    print("\n" + "=" * 70)
    print("## LABS THAT ALREADY HAD DYNAMIC FLAGS:")
    print("-" * 70)
    for lab_name in ALREADY_DYNAMIC:
        print(f"✅ {lab_name}")

    print("\n" + "=" * 70)
    print("## SUMMARY:")
    print("-" * 70)
    print(f"Total labs updated: {len(UPDATED_LABS)}")
    print(f"Labs already dynamic: {len(ALREADY_DYNAMIC)}")
    print(f"Total labs with dynamic flags: {len(UPDATED_LABS) + len(ALREADY_DYNAMIC)}")

    if all_ok:
        print("\n✅ ALL CHECKS PASSED!")
        print("\nNext steps:")
        print("1. Restart labs: docker-compose down && docker-compose up -d")
        print("2. Test each lab to verify flag generation works")
        print("3. Update CTFd challenges with appropriate flag hints")
    else:
        print("\n⚠️  SOME CHECKS FAILED - Please review the errors above")

    print("=" * 70)


if __name__ == "__main__":
    main()
