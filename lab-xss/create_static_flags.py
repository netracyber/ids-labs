#!/usr/bin/env python3
"""
Script to create CTFd flags with EXACT static flag values from labs.
This will create static flags (not regex) that match the actual flags in each lab.
"""

import requests
import json
import time

HOST = "72.61.140.122:8000"
CSRF_TOKEN = "d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee"
SESSION = "d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q"

headers = {
    "Accept-Language": "en-US,en;q=0.9",
    "Accept": "application/json",
    "Content-Type": "application/json",
    "CSRF-Token": CSRF_TOKEN,
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36",
    "Origin": f"http://{HOST}",
}

cookies = {
    "session": SESSION
}

# Static flags from each lab - exact values
STATIC_FLAGS = [
    {"challenge_id": 2, "name": "Search Query XSS Lab", "flag": "IDS{e9f8a3b2d1c4a6f7e8b9d3c5a1f2e4d6}"},
    {"challenge_id": 3, "name": "Attribute XSS Lab", "flag": "IDS{a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6}"},
    {"challenge_id": 4, "name": "JS String Context XSS Lab", "flag": "IDS{f1e2d3c4b5a6978f6e5d4c3b2a10987}"},
    {"challenge_id": 5, "name": "Document.write XSS Lab", "flag": "IDS{c4d3e2f1a6b7c8d9e0f1a2b3c4d5e6f}"},
    {"challenge_id": 6, "name": "innerHTML XSS Lab", "flag": "IDS{9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d}"},
    {"challenge_id": 7, "name": "DOM XSS in innerHTML with location.search", "flag": "IDS{e0b37cb9c327bc8a741bf11e6cd88025}"},
    {"challenge_id": 8, "name": "Formaction XSS Lab", "flag": "IDS{1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d}"},
    {"challenge_id": 9, "name": "DOM Hash XSS Lab", "flag": "IDS{ad46555c4b5829ca3c88b1bfaa171082}"},
    {"challenge_id": 10, "name": "Stored XSS Lab - HTML Context", "flag": "IDS{1c8a5c15517d898e873a11dd32a19fa4}"},
    {"challenge_id": 11, "name": "Stored XSS in anchor href attribute", "flag": "IDS{45f13c540e8997d935911c9987e167f6}"},
    {"challenge_id": 12, "name": "DOM-based XSS Lab - Document Location", "flag": "IDS{2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7}"},
    {"challenge_id": 13, "name": "Reflected XSS - Event Handler Attribute", "flag": "IDS{3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8}"},
    {"challenge_id": 14, "name": "Reflected XSS - JavaScript String Context", "flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}"},
    {"challenge_id": 15, "name": "Reflected XSS - Input Filter Bypass", "flag": "IDS{4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9}"},
    {"challenge_id": 16, "name": "DOM XSS Lab - document.write with location.search", "flag": "IDS{6326ea06ab28fe9c08cd27189395a62e}"},
]


def print_separator(char="=", length=80):
    print(char * length)


def create_static_flag(flag_info, index, total):
    """Create a static flag for a challenge"""
    url = f"http://{HOST}/api/v1/flags"

    # Use static flag type (not regex)
    flag_data = {
        "challenge_id": str(flag_info["challenge_id"]),
        "content": flag_info["flag"],  # Exact flag value
        "type": "static",  # Static type, not regex
        "data": ""
    }

    print_separator()
    print(f"[{index + 1}/{total}] Creating Static Flag for: {flag_info['name']}")
    print(f"Challenge ID: {flag_info['challenge_id']}")
    print(f"Flag Value: {flag_info['flag']}")
    print(f"Type: static")
    print_separator()

    try:
        flag_headers = headers.copy()
        flag_headers["Referer"] = f"http://{HOST}/admin/challenges/{flag_info['challenge_id']}"

        response = requests.post(url, headers=flag_headers, cookies=cookies, json=flag_data, timeout=30)

        print(f"\n📡 Response Status: {response.status_code} {response.reason}")

        if response.status_code == 200:
            try:
                data = response.json()
                print(json.dumps(data, indent=2))

                if data.get('success'):
                    print("\n" + "=" * 80)
                    print(f"✅ SUCCESS: Static flag created!")
                    print(f"   Challenge: {flag_info['name']}")
                    print(f"   Flag: {flag_info['flag']}")
                    print(f"   Flag ID: {data['data'].get('id', 'N/A')}")
                    print("=" * 80)
                    return True, None
                else:
                    print("\n❌ Response indicates failure")
                    return False, "Response success flag is False"
            except json.JSONDecodeError:
                print(f"Raw response: {response.text[:500]}")
                return False, "Invalid JSON response"
        else:
            print(response.text[:500])
            print("\n" + "=" * 80)
            print(f"❌ FAILED: Could not create flag")
            print("=" * 80)
            return False, f"HTTP {response.status_code}"

    except Exception as e:
        print(f"\n💥 EXCEPTION: {e}")
        return False, str(e)


def ask_continue(item_name, error_msg):
    print("\n" + "!" * 80)
    print(f"⚠️  Failed: {item_name}")
    print(f"   Error: {error_msg}")
    print("!" * 80)

    while True:
        choice = input("\nContinue? (y/n): ").lower().strip()
        if choice in ['y', 'yes']:
            return True
        elif choice in ['n', 'no']:
            return False
        else:
            print("Enter 'y' or 'n'")


def main():
    print("\n" + "=" * 80)
    print("🚀 CTFd Static Flag Creation for XSS Labs")
    print("=" * 80)
    print(f"Target: http://{HOST}/api/v1/flags")
    print(f"Total static flags to create: {len(STATIC_FLAGS)}")
    print("=" * 80 + "\n")

    # Track results
    results = {'success': [], 'failed': []}

    # Create flags one by one
    for i, flag_info in enumerate(STATIC_FLAGS):
        success, error = create_static_flag(flag_info, i, len(STATIC_FLAGS))

        if success:
            results['success'].append(flag_info['name'])
            print(f"\n✅ Moving to next flag...")
            time.sleep(0.5)
        else:
            results['failed'].append({'name': flag_info['name'], 'error': error})

            if not ask_continue(flag_info['name'], error):
                print("\n⛔ Stopping...")
                break

        print("\n")

    # Summary
    print_separator("=")
    print("📊 SUMMARY")
    print_separator("=")
    print(f"Total: {len(STATIC_FLAGS)}")
    print(f"✅ Success: {len(results['success'])}")
    print(f"❌ Failed: {len(results['failed'])}")
    print_separator("=")

    if results['success']:
        print("\n✅ Successfully Created:")
        for name in results['success']:
            print(f"   ✓ {name}")

    if results['failed']:
        print("\n❌ Failed:")
        for item in results['failed']:
            print(f"   ✗ {item['name']} - {item['error']}")

    print("\n" + "=" * 80)


if __name__ == "__main__":
    main()
