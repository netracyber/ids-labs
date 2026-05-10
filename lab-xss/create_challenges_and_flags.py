#!/usr/bin/env python3
"""
Complete Script to Create CTFd Challenges AND Flags
This script creates challenges and then automatically creates flags for each challenge.
Update SESSION and CSRF_TOKEN with your browser values.
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
    "Referer": f"http://{HOST}/admin/challenges/new",
}

cookies = {
    "session": SESSION
}

# Challenges with their flag regex patterns
# Using regex type allows any flag matching the pattern to be accepted
challenges = [
    {
        "name": "Search Query XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS (Easy) - Lab ini mensimulasikan aplikasi pencarian sederhana dengan kerentanan Reflected XSS melalui query parameter. Aplikasi "QuickSearch Pro" menampilkan hasil pencarian dengan merfleksikan input pengguna tanpa sanitasi. Tujuan: Memahami konsep dasar Reflected XSS, mengidentifikasi parameter vulnerable, menyusun payload XSS dasar. Parameter: q. Context: HTML body. Flag: Dynamic - Changes on container restart. Format: IDS{32 hexadecimal characters}""",
        "value": "100",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "Attribute XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS in HTML Attribute (Easy) - Lab mensimulasikan formulir pencarian dengan kerentanan Reflected XSS melalui HTML attribute context. Aplikasi "SecureForm Pro" menampilkan input pengguna di dalam atribut HTML (value="" attribute) tanpa sanitasi. Tujuan: Memahami Reflected XSS dalam HTML attribute context, belajar attribute encoding, teknik keluar dari attribute context, event handler untuk eksekusi JavaScript. Parameter: search. Context: HTML attribute. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "100",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "JS String Context XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS in JavaScript String (Easy) - Lab mensimulasikan aplikasi messaging dengan kerentanan Reflected XSS melalui JavaScript string context. Aplikasi "MessageBoard Pro" menampilkan input pengguna di dalam JavaScript string variable tanpa sanitasi. Tujuan: Memahami Reflected XSS dalam JavaScript string context, perbedaan injeksi di berbagai konteks, cara keluar dari string context, JavaScript encoding. Parameter: message. Context: JavaScript string. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "150",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "Document.write XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS via document.write() (Easy) - Lab mensimulasikan aplikasi dynamic rendering dengan kerentanan Reflected XSS melalui fungsi document.write(). Aplikasi "DynamicPage Pro" merender input pengguna menggunakan document.write() tanpa sanitasi. Tujuan: Memahami Reflected XSS melalui document.write(), bagaimana document.write() dapat mengeksekusi JavaScript, risiko keamanan dari penggunaan document.write(). Parameter: content. Context: document.write() function. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "150",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "innerHTML XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS via innerHTML injection (Easy) - Lab mensimulasikan aplikasi note-taking dengan kerentanan Reflected XSS melalui properti innerHTML. Aplikasi "QuickNote Pro" merender input pengguna menggunakan element.innerHTML tanpa sanitasi. Tujuan: Memahami Reflected XSS melalui innerHTML, perbedaan innerHTML dengan textContent, risiko keamanan penggunaan innerHTML, praktik terbaik manipulasi DOM aman. Parameter: note. Context: innerHTML property. Payload: <img src=x onerror=alert(1)>. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "150",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "DOM XSS in innerHTML with location.search",
        "category": "XSS",
        "description": """DOM-based XSS (Easy) - Lab mendemonstrasikan kerentanan DOM-based XSS di innerHTML assignment menggunakan data dari location.search. Challenge berisi kerentanan di search blog functionality. Tugas: Temukan dan eksploitasi XSS vulnerability untuk mengeksekusi JavaScript code di browser victim. Kerentanan memungkinkan inject malicious HTML dan JavaScript code melalui search parameter. Find a way to execute alert function untuk capture flag. Source: location.search. Sink: innerHTML. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "200",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "Formaction XSS Lab",
        "category": "XSS",
        "description": """POST-based XSS via HTML5 formaction attribute (Easy) - Lab demonstrates XSS vulnerability through POST parameter injection using HTML5's formaction attribute. Application accepts user input via POST request and reflects it in search result page inside value attribute. With proper payload crafting, escape the attribute and inject a formaction attribute with javascript: URI. Flag disimpan di cookie bernama xss_flag. Method: POST. Context: Attribute context. Execution: formaction attribute with javascript: URI. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "200",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "DOM Hash XSS Lab",
        "category": "XSS",
        "description": """DOM-based XSS via location.hash + innerHTML (Easy) - Lab demonstrates DOM-based XSS vulnerability where user input from URL fragment (hash) is unsafely inserted into page using innerHTML property. Application reads location.hash (part after # in URL) and directly assigns to element's innerHTML without sanitization. Source: location.hash. Sink: element.innerHTML. Important: Payload never reaches server, traditional server-side filters won't catch it, can bypass WAF. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "200",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "Stored XSS Lab - HTML Context",
        "category": "XSS",
        "description": """Stored XSS in HTML Context (Easy) - Lab demonstrates stored cross-site scripting vulnerability where user input is stored and reflected in HTML context without any encoding. Vulnerability exists in comment functionality where user comments are stored without sanitization and displayed directly in HTML without proper encoding. PHP code stores user comments and displays them without sanitization. Payload: <script>alert('XSS')</script>. Context: HTML body. Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "250",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "Stored XSS in anchor href attribute",
        "category": "XSS",
        "description": """Stored XSS into anchor href with double quotes HTML-encoded (Medium) - Lab contains stored XSS vulnerability in comment functionality. Vulnerability occurs when user input from "Website" field is stored and reflected in anchor href attribute without proper sanitization. Although double quotes are HTML-encoded, application is still vulnerable to JavaScript URL injection. Vulnerable code: <a href="<?php echo $website; ?>"><?php echo $author; ?></a>. Context: Anchor href attribute. Payload: javascript:alert(1). Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "250",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
    {
        "name": "DOM-based XSS Lab - Document Location",
        "category": "XSS",
        "description": """DOM-based XSS (Medium) - Lab demonstrates DOM-based XSS vulnerability where data from URL (through document.location) diambil dan dimasukkan ke dalam DOM menggunakan metode yang tidak aman. Tidak ada refleksi server-side - kerentanan sepenuhnya berada di sisi klien. Tujuan: Memahami DOM-based XSS dan perbedaan dari reflected XSS, sources DOM (document.location, document.URL, window.location), sinks DOM berbahaya (innerHTML, eval(), document.write()). Source: document.location. Flag: Dynamic - Format: IDS{20-32 alphanumeric characters}""",
        "value": "300",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-zA-Z0-9]{20,32}\}"
    },
    {
        "name": "Reflected XSS - Event Handler Attribute",
        "category": "XSS",
        "description": """Reflected XSS via Event Handler Attribute (Medium) - Lab demonstrates kerentanan Reflected XSS melalui atribut event handler. Kerentanan terjadi ketika input pengguna direfleksikan ke dalam nilai atribut event handler HTML seperti onerror, onload, onclick, dll., yang memerlukan teknik khusus untuk keluar dari konteks atribut. Tujuan: Memahami konteks atribut HTML, event handler attributes, cara keluar dari atribut, encoding dalam atribut. Context: Event handler attribute. Flag: Dynamic - Format: IDS{20-32 alphanumeric characters}""",
        "value": "300",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-zA-Z0-9]{20,32}\}"
    },
    {
        "name": "Reflected XSS - JavaScript String Context",
        "category": "XSS",
        "description": """Reflected XSS in JavaScript String Context (Medium) - Lab demonstrates kerentanan Reflected XSS dalam konteks string JavaScript. Berbeda dengan XSS pada HTML biasa, kerentanan ini terjadi ketika input pengguna direfleksikan ke dalam literal string JavaScript, memerlukan teknik khusus untuk keluar dari konteks string. Tujuan: Memahami konteks injeksi yang berbeda (HTML vs JavaScript string), cara keluar dari string literal (single quote, double quote, backtick), terminasi statement JavaScript. Context: JavaScript string. Flag: Dynamic - Format: IDS{20-32 alphanumeric characters}""",
        "value": "300",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-zA-Z0-9]{20,32}\}"
    },
    {
        "name": "Reflected XSS - Input Filter Bypass",
        "category": "XSS",
        "description": """Reflected XSS with Basic Filter Bypass (Medium) - Lab demonstrates kerentanan Reflected XSS dengan mekanisme filter input dasar yang dapat di-bypass. Filter memblokir tag dan event handler umum, namun memiliki celah yang memungkinkan eksekusi payload dengan teknik yang tepat. Tujuan: Memahami bagaimana filter input XSS bekerja dan keterbatasannya, teknik bypass filter dasar menggunakan variasi encoding, konteks injeksi yang berbeda, pentingnya output encoding. Context: Varies, filter bypass required. Flag: Dynamic - Format: IDS{20-32 alphanumeric characters}""",
        "value": "350",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-zA-Z0-9]{20,32}\}"
    },
    {
        "name": "DOM XSS Lab - document.write with location.search",
        "category": "XSS",
        "description": """DOM-based XSS via document.write() (Medium) - Lab demonstrates DOM-based cross-site scripting vulnerability using JavaScript document.write() function with data from location.search. Lab simulates search query tracking functionality vulnerable to DOM-based XSS. Vulnerability exists in JavaScript code using document.write() with data from location.search, controlled via URL. Payload: <script>alert('XSS')</script>. Source: location.search. Sink: document.write(). Flag: Dynamic - Format: IDS{32 hex characters}""",
        "value": "250",
        "state": "hidden",
        "type": "standard",
        "flag_regex": r"IDS\{[a-f0-9]{32}\}"
    },
]


def print_separator(char="=", length=80):
    """Print a separator line"""
    print(char * length)


def create_challenge(challenge, index, total):
    """Create a single challenge"""
    url = f"http://{HOST}/api/v1/challenges"

    # Remove flag_regex from challenge data before sending
    challenge_data = {k: v for k, v in challenge.items() if k != 'flag_regex'}

    print_separator()
    print(f"[{index + 1}/{total}] Creating Challenge: {challenge['name']}")
    print(f"Category: {challenge['category']} | Points: {challenge['value']}")
    print_separator()

    try:
        response = requests.post(url, headers=headers, cookies=cookies, json=challenge_data, timeout=30)

        print(f"\n📡 Response Status: {response.status_code} {response.reason}")

        if response.status_code == 200:
            try:
                data = response.json()
                print(json.dumps(data, indent=2))

                if data.get('success') and 'data' in data:
                    challenge_data = data['data']
                    print("\n" + "=" * 80)
                    print(f"✅ SUCCESS: Challenge created successfully!")
                    print(f"   Challenge ID: {challenge_data.get('id')}")
                    print(f"   Name: {challenge_data.get('name')}")
                    print("=" * 80)

                    return True, challenge_data.get('id'), None
                else:
                    print("\n❌ UNEXPECTED: Response indicates failure")
                    return False, None, "Response success flag is False"
            except json.JSONDecodeError:
                print(f"⚠️  Could not parse JSON response")
                print(f"Raw response: {response.text[:500]}")
                return False, None, "Invalid JSON response"
        else:
            try:
                error_data = response.json()
                print(json.dumps(error_data, indent=2))
            except:
                print(response.text)

            print("\n" + "=" * 80)
            print(f"❌ FAILED: Could not create challenge")
            print("=" * 80)

            return False, None, f"HTTP {response.status_code}: {response.reason}"

    except Exception as e:
        print(f"\n💥 EXCEPTION: {type(e).__name__}: {e}")
        return False, None, str(e)


def create_flag(challenge_id, challenge_name, flag_regex):
    """Create a flag for a challenge"""
    url = f"http://{HOST}/api/v1/flags"

    flag_data = {
        "challenge_id": str(challenge_id),
        "content": flag_regex,
        "type": "regex",
        "data": ""
    }

    print(f"\n🚩 Creating Flag for Challenge ID {challenge_id}...")
    print(f"   Type: regex")
    print(f"   Pattern: {flag_regex}")

    try:
        # Update referer for flag creation
        flag_headers = headers.copy()
        flag_headers["Referer"] = f"http://{HOST}/admin/challenges/{challenge_id}"

        response = requests.post(url, headers=flag_headers, cookies=cookies, json=flag_data, timeout=30)

        print(f"   Response Status: {response.status_code}")

        if response.status_code == 200:
            data = response.json()
            if data.get('success'):
                print(f"   ✅ Flag created successfully!")
                return True, None
            else:
                return False, "Response indicates failure"
        else:
            print(f"   Response: {response.text[:200]}")
            return False, f"HTTP {response.status_code}"

    except Exception as e:
        print(f"   ❌ Error: {e}")
        return False, str(e)


def ask_continue(item_name, error_msg, item_type="challenge"):
    """Ask user if they want to continue after failure"""
    print("\n" + "!" * 80)
    print(f"⚠️  Failed to create {item_type}: {item_name}")
    print(f"   Error: {error_msg}")
    print("!" * 80)

    while True:
        choice = input("\nDo you want to continue with the next item? (y/n): ").lower().strip()
        if choice == 'y' or choice == 'yes':
            return True
        elif choice == 'n' or choice == 'no':
            return False
        else:
            print("Invalid choice. Please enter 'y' to continue or 'n' to stop.")


def main():
    """Main function to create all challenges and flags"""
    print("\n" + "=" * 80)
    print("🚀 CTFd Challenge AND Flag Creation Script")
    print("=" * 80)
    print(f"Target: http://{HOST}")
    print(f"Total challenges to create: {len(challenges)}")
    print("=" * 80 + "\n")

    # Verify connection
    print("🔍 Testing connection to CTFd...")
    try:
        test_url = f"http://{HOST}/api/v1/challenges"
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
        'challenges': {'success': [], 'failed': []},
        'flags': {'success': [], 'failed': []}
    }

    # Process challenges one by one
    for i, challenge in enumerate(challenges):
        # Create challenge
        success, challenge_id, error = create_challenge(challenge, i, len(challenges))

        if success:
            results['challenges']['success'].append(challenge['name'])

            # Create flag for this challenge
            flag_success, flag_error = create_flag(challenge_id, challenge['name'], challenge['flag_regex'])

            if flag_success:
                results['flags']['success'].append(challenge['name'])
            else:
                results['flags']['failed'].append({
                    'name': challenge['name'],
                    'error': flag_error
                })

            print(f"\n✅ Moving to next challenge in 1 second...")
            time.sleep(1)
        else:
            results['challenges']['failed'].append({
                'name': challenge['name'],
                'error': error
            })

            # Ask user what to do
            should_continue = ask_continue(challenge['name'], error, "challenge")

            if not should_continue:
                print("\n⛔ Stopping script execution...")
                break

        print("\n")

    # Print final summary
    print_separator("=")
    print("📊 FINAL SUMMARY")
    print_separator("=")

    print("\n📋 Challenges:")
    print(f"   ✅ Successfully created: {len(results['challenges']['success'])}")
    print(f"   ❌ Failed: {len(results['challenges']['failed'])}")

    print("\n🚩 Flags:")
    print(f"   ✅ Successfully created: {len(results['flags']['success'])}")
    print(f"   ❌ Failed: {len(results['flags']['failed'])}")

    print_separator()

    if results['challenges']['success']:
        print("\n✅ Successfully Created Challenges:")
        for name in results['challenges']['success']:
            print(f"   ✓ {name}")

    if results['challenges']['failed']:
        print("\n❌ Failed Challenges:")
        for item in results['challenges']['failed']:
            print(f"   ✗ {item['name']}")
            print(f"      Error: {item['error']}")

    if results['flags']['failed']:
        print("\n⚠️  Flags That Failed (but challenge was created):")
        for item in results['flags']['failed']:
            print(f"   ⚠️  {item['name']}")
            print(f"      Error: {item['error']}")

    print_separator()
    print("\n✨ Script completed!")
    print("\n📝 Note:")
    print("   - All flags use 'regex' type to accept dynamic flags")
    print("   - Any flag matching the pattern will be accepted")
    print("   - Students can submit any valid flag from the running labs")
    print("\n💡 Tip: You can manually add more flags later if needed via the CTFd admin panel")


if __name__ == "__main__":
    main()
