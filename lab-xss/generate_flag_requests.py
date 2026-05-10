#!/usr/bin/env python3
"""
Script to create CTFd flags with EXACT static flag values from labs.
This creates static flags (not regex) that match the actual flags in each lab.
"""

import json
import requests
import os
from pathlib import Path

# Configuration - Update these values from your browser
HOST = "72.61.140.122:8000"
CSRF_TOKEN = "d0a5632ff03824c110c32306fed24f51bf9a5bdd9c0f280d8d3789e14a5531ee"
SESSION_COOKIE = "d9816a04-1675-49d2-b205-9e57b2f8b1fb.6294QEjHBbnd1OSoidwtxGFqH5Q"

# Challenge IDs from your successful creation with EXACT static flag values
CHALLENGES = [
    {"id": 2, "name": "Search Query XSS Lab", "flag": "IDS{e9f8a3b2d1c4a6f7e8b9d3c5a1f2e4d6}"},
    {"id": 3, "name": "Attribute XSS Lab", "flag": "IDS{a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6}"},
    {"id": 4, "name": "JS String Context XSS Lab", "flag": "IDS{f1e2d3c4b5a6978f6e5d4c3b2a10987}"},
    {"id": 5, "name": "Document.write XSS Lab", "flag": "IDS{c4d3e2f1a6b7c8d9e0f1a2b3c4d5e6f}"},
    {"id": 6, "name": "innerHTML XSS Lab", "flag": "IDS{9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d}"},
    {"id": 7, "name": "DOM XSS in innerHTML with location.search", "flag": "IDS{e0b37cb9c327bc8a741bf11e6cd88025}"},
    {"id": 8, "name": "Formaction XSS Lab", "flag": "IDS{1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d}"},
    {"id": 9, "name": "DOM Hash XSS Lab", "flag": "IDS{ad46555c4b5829ca3c88b1bfaa171082}"},
    {"id": 10, "name": "Stored XSS Lab - HTML Context", "flag": "IDS{1c8a5c15517d898e873a11dd32a19fa4}"},
    {"id": 11, "name": "Stored XSS in anchor href attribute", "flag": "IDS{45f13c540e8997d935911c9987e167f6}"},
    {"id": 12, "name": "DOM-based XSS Lab - Document Location", "flag": "IDS{2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7}"},
    {"id": 13, "name": "Reflected XSS - Event Handler Attribute", "flag": "IDS{3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8}"},
    {"id": 14, "name": "Reflected XSS - JavaScript String Context", "flag": "IDS{92798f74bc5cb240a73f2c9a8660c5ef}"},
    {"id": 15, "name": "Reflected XSS - Input Filter Bypass", "flag": "IDS{4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9}"},
    {"id": 16, "name": "DOM XSS Lab - document.write with location.search", "flag": "IDS{6326ea06ab28fe9c08cd27189395a62e}"},
]

headers = {
    "Accept-Language": "en-US,en;q=0.9",
    "Accept": "application/json",
    "Content-Type": "application/json",
    "CSRF-Token": CSRF_TOKEN,
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36",
    "Origin": f"http://{HOST}",
}

cookies = {
    "session": SESSION_COOKIE
}


def print_separator(char="=", length=80):
    """Print a separator line"""
    print(char * length)


def create_flag(challenge):
    """Create a static flag for a challenge"""
    url = f"http://{HOST}/api/v1/flags"

    # Use static flag type (not regex)
    flag_data = {
        "challenge_id": str(challenge["id"]),
        "content": challenge["flag"],  # Exact flag value
        "type": "static",  # Static type, not regex
        "data": ""
    }

    print_separator()
    print(f"[{challenge['id']}] Creating Static Flag for: {challenge['name']}")
    print(f"Challenge ID: {challenge['id']}")
    print(f"Flag Value: {challenge['flag']}")
    print(f"Type: static")
    print_separator()

    try:
        # Update referer for flag creation
        flag_headers = headers.copy()
        flag_headers["Referer"] = f"http://{HOST}/admin/challenges/{challenge['id']}"

        response = requests.post(url, headers=flag_headers, cookies=cookies, json=flag_data, timeout=30)

        print(f"\n📡 Response Status: {response.status_code} {response.reason}")

        if response.status_code == 200:
            try:
                data = response.json()
                print(json.dumps(data, indent=2))

                if data.get('success'):
                    print("\n" + "=" * 80)
                    print(f"✅ SUCCESS: Static flag created!")
                    print(f"   Challenge: {challenge['name']}")
                    print(f"   Flag: {challenge['flag']}")
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

    except requests.exceptions.Timeout:
        print(f"\n⏱️  TIMEOUT: Request took too long")
        return False, "Request timeout"
    except requests.exceptions.ConnectionError as e:
        print(f"\n🔌 CONNECTION ERROR: {e}")
        return False, f"Connection error: {e}"
    except Exception as e:
        print(f"\n💥 EXCEPTION: {type(e).__name__}: {e}")
        import traceback
        print("\nFull traceback:")
        traceback.print_exc()
        return False, str(e)


def ask_continue(item_name, error_msg):
    """Ask user if they want to continue after failure"""
    print("\n" + "!" * 80)
    print(f"⚠️  Failed to create flag for: {item_name}")
    print(f"   Error: {error_msg}")
    print("!" * 80)

    while True:
        choice = input("\nDo you want to continue with the next flag? (y/n): ").lower().strip()
        if choice == 'y' or choice == 'yes':
            return True
        elif choice == 'n' or choice == 'no':
            return False
        else:
            print("Invalid choice. Please enter 'y' to continue or 'n' to stop.")


def generate_curl_commands():
    """Generate cURL commands for creating static flags"""
    base_dir = Path("/home/labuser/tools/lab-xss/challenge_requests")
    base_dir.mkdir(exist_ok=True)

    output = "# CTFd Static Flag Creation - cURL Commands\n\n"
    output += f"# Host: {HOST}\n"
    output += f"# Total Challenges: {len(CHALLENGES)}\n"
    output += f"# Flag Type: static (exact match)\n\n"

    for challenge in CHALLENGES:
        flag_data = {
            "challenge_id": str(challenge["id"]),
            "content": challenge["flag"],  # Exact flag value
            "type": "static",
            "data": ""
        }
        json_payload = json.dumps(flag_data, separators=(',', ':'))

        output += f"# {challenge['id']}. {challenge['name']}\n"
        output += f"# Flag: {challenge['flag']}\n"
        output += f'''curl -X POST 'http://{HOST}/api/v1/flags' \\
  -H 'Accept: application/json' \\
  -H 'Content-Type: application/json' \\
  -H 'CSRF-Token: {CSRF_TOKEN}' \\
  -H 'Origin: http://{HOST}' \\
  -H 'Referer: http://{HOST}/admin/challenges/{challenge["id"]}' \\
  -H 'Cookie: session={SESSION_COOKIE}' \\
  --data-raw '{json_payload}'
'''
        output += "\n"

    curl_file = base_dir / "create_static_flags_curl.sh"
    curl_file.write_text(output)
    print(f"✓ Generated: {curl_file}")


def generate_raw_http_requests():
    """Generate raw HTTP requests for creating static flags"""
    base_dir = Path("/home/labuser/tools/lab-xss/challenge_requests")
    base_dir.mkdir(exist_ok=True)

    output = "# Raw HTTP Requests for CTFd Static Flag Creation\n\n"

    for challenge in CHALLENGES:
        flag_data = {
            "challenge_id": str(challenge["id"]),
            "content": challenge["flag"],  # Exact flag value
            "type": "static",
            "data": ""
        }
        json_payload = json.dumps(flag_data, separators=(',', ':'))

        output += f"# {challenge['id']}. {challenge['name']}\n"
        output += f"# Flag: {challenge['flag']}\n"
        output += f'''POST /api/v1/flags HTTP/1.1
Host: {HOST}
Content-Length: {len(json_payload)}
Accept-Language: en-US,en;q=0.9
Accept: application/json
Content-Type: application/json
CSRF-Token: {CSRF_TOKEN}
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36
Origin: http://{HOST}
Referer: http://{HOST}/admin/challenges/{challenge["id"]}
Accept-Encoding: gzip, deflate, br
Cookie: session={SESSION_COOKIE}
Connection: keep-alive

{json_payload}

'''
        output += "\n" + "="*80 + "\n\n"

    raw_file = base_dir / "create_static_flags_http.txt"
    raw_file.write_text(output)
    print(f"✓ Generated: {raw_file}")


def generate_summary_json():
    """Generate JSON summary of all flags"""
    base_dir = Path("/home/labuser/tools/lab-xss/challenge_requests")
    base_dir.mkdir(exist_ok=True)

    summary = {
        "host": HOST,
        "csrf_token": CSRF_TOKEN,
        "session_cookie": SESSION_COOKIE,
        "total_challenges": len(CHALLENGES),
        "flag_type": "static",
        "challenges": [
            {
                "id": ch["id"],
                "name": ch["name"],
                "flag": ch["flag"]
            }
            for ch in CHALLENGES
        ]
    }

    summary_file = base_dir / "static_flags_summary.json"
    summary_file.write_text(json.dumps(summary, indent=2))
    print(f"✓ Generated: {summary_file}")


def main():
    """Main function to create all static flags"""
    print("\n" + "=" * 80)
    print("🚀 CTFd Static Flag Creation Script for XSS Challenges")
    print("=" * 80)
    print(f"Target: http://{HOST}/api/v1/flags")
    print(f"Total flags to create: {len(CHALLENGES)}")
    print(f"Flag Type: static (exact match, not regex)")
    print("=" * 80 + "\n")

    # First, generate the request files
    print("📄 Generating request files...")
    generate_curl_commands()
    generate_raw_http_requests()
    generate_summary_json()
    print()

    # Verify connection
    print("🔍 Testing connection to CTFd...")
    try:
        test_url = f"http://{HOST}/api/v1/flags"
        test_response = requests.get(test_url, headers=headers, cookies=cookies, timeout=10)
        if test_response.status_code in [200, 401, 403]:
            print(f"✅ Connection successful (status: {test_response.status_code})")
        else:
            print(f"⚠️  Warning: Unexpected status {test_response.status_code}")
    except Exception as e:
        print(f"❌ Connection failed: {e}")
        cont = input("\nContinue anyway? (y/n): ").lower()
        if cont != 'y':
            return

    print("\n")

    # Track results
    results = {
        'success': [],
        'failed': []
    }

    # Process flags one by one
    for challenge in CHALLENGES:
        success, error = create_flag(challenge)

        if success:
            results['success'].append(challenge['name'])
            print(f"\n✅ Moving to next flag...")
            import time
            time.sleep(0.5)
        else:
            results['failed'].append({
                'name': challenge['name'],
                'error': error
            })

            # Ask user what to do
            should_continue = ask_continue(challenge['name'], error)

            if not should_continue:
                print("\n⛔ Stopping script execution...")
                break

        print("\n")

    # Print final summary
    print_separator("=")
    print("📊 FINAL SUMMARY")
    print_separator("=")
    print(f"Total flags: {len(CHALLENGES)}")
    print(f"✅ Successfully created: {len(results['success'])}")
    print(f"❌ Failed: {len(results['failed'])}")
    print_separator()

    if results['success']:
        print("\n✅ Successfully Created:")
        for name in results['success']:
            print(f"   ✓ {name}")

    if results['failed']:
        print("\n❌ Failed to Create:")
        for item in results['failed']:
            print(f"   ✗ {item['name']}")
            print(f"      Error: {item['error']}")

    print_separator()
    print("\n✨ Script completed!")
    print("\n📝 Note:")
    print("   - All flags are STATIC with exact values")
    print("   - Flags in CTFd match exactly with flags in labs")
    print("   - Users submit the flag they get from the lab")
    print("   - No regex pattern matching needed")
    print("\n📁 Generated files:")
    print("   - challenge_requests/create_static_flags_curl.sh")
    print("   - challenge_requests/create_static_flags_http.txt")
    print("   - challenge_requests/static_flags_summary.json")


if __name__ == "__main__":
    main()
