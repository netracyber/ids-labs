#!/usr/bin/env python3
"""
Script to create CTFd challenges automatically using requests library.
Update SESSION and CSRF_TOKEN with your browser values.
"""

import requests
import json

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

challenges = [
    {
        "name": "Search Query XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS (Easy) - Lab ini mensimulasikan aplikasi pencarian sederhana dengan kerentanan Reflected XSS melalui query parameter. Aplikasi "QuickSearch Pro" menampilkan hasil pencarian dengan merfleksikan input pengguna tanpa sanitasi. Tujuan: Memahami konsep dasar Reflected XSS, mengidentifikasi parameter vulnerable, menyusun payload XSS dasar. Parameter: q. Context: HTML body.""",
        "value": "100",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Attribute XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS in HTML Attribute (Easy) - Lab mensimulasikan formulir pencarian dengan kerentanan Reflected XSS melalui HTML attribute context. Aplikasi "SecureForm Pro" menampilkan input pengguna di dalam atribut HTML (value="" attribute) tanpa sanitasi. Tujuan: Memahami Reflected XSS dalam HTML attribute context, belajar attribute encoding, teknik keluar dari attribute context, event handler untuk eksekusi JavaScript. Parameter: search. Context: HTML attribute.""",
        "value": "100",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "JS String Context XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS in JavaScript String (Easy) - Lab mensimulasikan aplikasi messaging dengan kerentanan Reflected XSS melalui JavaScript string context. Aplikasi "MessageBoard Pro" menampilkan input pengguna di dalam JavaScript string variable tanpa sanitasi. Tujuan: Memahami Reflected XSS dalam JavaScript string context, perbedaan injeksi di berbagai konteks, cara keluar dari string context, JavaScript encoding. Parameter: message. Context: JavaScript string.""",
        "value": "150",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Document.write XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS via document.write() (Easy) - Lab mensimulasikan aplikasi dynamic rendering dengan kerentanan Reflected XSS melalui fungsi document.write(). Aplikasi "DynamicPage Pro" merender input pengguna menggunakan document.write() tanpa sanitasi. Tujuan: Memahami Reflected XSS melalui document.write(), bagaimana document.write() dapat mengeksekusi JavaScript, risiko keamanan dari penggunaan document.write(). Parameter: content. Context: document.write() function.""",
        "value": "150",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "innerHTML XSS Lab",
        "category": "XSS",
        "description": """Reflected XSS via innerHTML injection (Easy) - Lab mensimulasikan aplikasi note-taking dengan kerentanan Reflected XSS melalui properti innerHTML. Aplikasi "QuickNote Pro" merender input pengguna menggunakan element.innerHTML tanpa sanitasi. Tujuan: Memahami Reflected XSS melalui innerHTML, perbedaan innerHTML dengan textContent, risiko keamanan penggunaan innerHTML, praktik terbaik manipulasi DOM aman. Parameter: note. Context: innerHTML property. Payload: <img src=x onerror=alert(1)>""",
        "value": "150",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "DOM XSS in innerHTML with location.search",
        "category": "XSS",
        "description": """DOM-based XSS (Easy) - Lab mendemonstrasikan kerentanan DOM-based XSS di innerHTML assignment menggunakan data dari location.search. Challenge berisi kerentanan di search blog functionality. Tugas: Temukan dan eksploitasi XSS vulnerability untuk mengeksekusi JavaScript code di browser victim. Kerentanan memungkinkan inject malicious HTML dan JavaScript code melalui search parameter. Find a way to execute alert function untuk capture flag. Source: location.search. Sink: innerHTML.""",
        "value": "200",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Formaction XSS Lab",
        "category": "XSS",
        "description": """POST-based XSS via HTML5 formaction attribute (Easy) - Lab demonstrates XSS vulnerability through POST parameter injection using HTML5's formaction attribute. Application accepts user input via POST request and reflects it in search result page inside value attribute. With proper payload crafting, escape the attribute and inject a formaction attribute with javascript: URI. Flag disimpan di cookie bernama xss_flag. Method: POST. Context: Attribute context. Execution: formaction attribute with javascript: URI.""",
        "value": "200",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "DOM Hash XSS Lab",
        "category": "XSS",
        "description": """DOM-based XSS via location.hash + innerHTML (Easy) - Lab demonstrates DOM-based XSS vulnerability where user input from URL fragment (hash) is unsafely inserted into page using innerHTML property. Application reads location.hash (part after # in URL) and directly assigns to element's innerHTML without sanitization. Source: location.hash. Sink: element.innerHTML. Important: Payload never reaches server, traditional server-side filters won't catch it, can bypass WAF. Flag format: IDS{********}.""",
        "value": "200",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Stored XSS Lab - HTML Context",
        "category": "XSS",
        "description": """Stored XSS in HTML Context (Easy) - Lab demonstrates stored cross-site scripting vulnerability where user input is stored and reflected in HTML context without any encoding. Vulnerability exists in comment functionality where user comments are stored without sanitization and displayed directly in HTML without proper encoding. PHP code stores user comments and displays them without sanitization. Flag: IDS{1c8a5c15517d898e873a11dd32a19fa4}. Payload: <script>alert('XSS')</script>. Context: HTML body.""",
        "value": "250",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Stored XSS in anchor href attribute",
        "category": "XSS",
        "description": """Stored XSS into anchor href with double quotes HTML-encoded (Medium) - Lab contains stored XSS vulnerability in comment functionality. Vulnerability occurs when user input from "Website" field is stored and reflected in anchor href attribute without proper sanitization. Although double quotes are HTML-encoded, application is still vulnerable to JavaScript URL injection. Vulnerable code: <a href="<?php echo $website; ?>"><?php echo $author; ?></a>. Flag: IDS{45f13c540e8997d935911c9987e167f6}. Context: Anchor href attribute. Payload: javascript:alert(1).""",
        "value": "250",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "DOM-based XSS Lab - Document Location",
        "category": "XSS",
        "description": """DOM-based XSS (Medium) - Lab demonstrates DOM-based XSS vulnerability where data from URL (through document.location) diambil dan dimasukkan ke dalam DOM menggunakan metode yang tidak aman. Tidak ada refleksi server-side - kerentanan sepenuhnya berada di sisi klien. Tujuan: Memahami DOM-based XSS dan perbedaan dari reflected XSS, sources DOM (document.location, document.URL, window.location), sinks DOM berbahaya (innerHTML, eval(), document.write()). Flag format: IDS{************************}. Source: document.location.""",
        "value": "300",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Reflected XSS - Event Handler Attribute",
        "category": "XSS",
        "description": """Reflected XSS via Event Handler Attribute (Medium) - Lab demonstrates kerentanan Reflected XSS melalui atribut event handler. Kerentanan terjadi ketika input pengguna direfleksikan ke dalam nilai atribut event handler HTML seperti onerror, onload, onclick, dll., yang memerlukan teknik khusus untuk keluar dari konteks atribut. Tujuan: Memahami konteks atribut HTML, event handler attributes, cara keluar dari atribut, encoding dalam atribut. Flag format: IDS{************************}. Context: Event handler attribute.""",
        "value": "300",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Reflected XSS - JavaScript String Context",
        "category": "XSS",
        "description": """Reflected XSS in JavaScript String Context (Medium) - Lab demonstrates kerentanan Reflected XSS dalam konteks string JavaScript. Berbeda dengan XSS pada HTML biasa, kerentanan ini terjadi ketika input pengguna direfleksikan ke dalam literal string JavaScript, memerlukan teknik khusus untuk keluar dari konteks string. Tujuan: Memahami konteks injeksi yang berbeda (HTML vs JavaScript string), cara keluar dari string literal (single quote, double quote, backtick), terminasi statement JavaScript. Flag format: IDS{************************}. Context: JavaScript string.""",
        "value": "300",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "Reflected XSS - Input Filter Bypass",
        "category": "XSS",
        "description": """Reflected XSS with Basic Filter Bypass (Medium) - Lab demonstrates kerentanan Reflected XSS dengan mekanisme filter input dasar yang dapat di-bypass. Filter memblokir tag dan event handler umum, namun memiliki celah yang memungkinkan eksekusi payload dengan teknik yang tepat. Tujuan: Memahami bagaimana filter input XSS bekerja dan keterbatasannya, teknik bypass filter dasar menggunakan variasi encoding, konteks injeksi yang berbeda, pentingnya output encoding. Flag format: IDS{************************}. Context: Varies, filter bypass required.""",
        "value": "350",
        "state": "hidden",
        "type": "standard"
    },
    {
        "name": "DOM XSS Lab - document.write with location.search",
        "category": "XSS",
        "description": """DOM-based XSS via document.write() (Medium) - Lab demonstrates DOM-based cross-site scripting vulnerability using JavaScript document.write() function with data from location.search. Lab simulates search query tracking functionality vulnerable to DOM-based XSS. Vulnerability exists in JavaScript code using document.write() with data from location.search, controlled via URL. Flag: IDS{6326ea06ab28fe9c08cd27189395a62e}. Payload: <script>alert('XSS')</script>. Source: location.search. Sink: document.write().""",
        "value": "250",
        "state": "hidden",
        "type": "standard"
    },
]

def create_challenge(challenge):
    """Create a single challenge"""
    url = f"http://{HOST}/api/v1/challenges"

    try:
        response = requests.post(url, headers=headers, cookies=cookies, json=challenge)

        if response.status_code == 200:
            data = response.json()
            print(f"✓ Success: {challenge['name']}")
            print(f"  Challenge ID: {data['data']['id']}")
            return True
        else:
            print(f"✗ Failed: {challenge['name']}")
            print(f"  Status: {response.status_code}")
            print(f"  Response: {response.text}")
            return False
    except Exception as e:
        print(f"✗ Error: {challenge['name']}")
        print(f"  Exception: {e}")
        return False

def main():
    """Main function to create all challenges"""
    print(f"Creating {len(challenges)} challenges...")
    print("=" * 50)

    success_count = 0
    failed_count = 0

    for challenge in challenges:
        if create_challenge(challenge):
            success_count += 1
        else:
            failed_count += 1
        print()

    print("=" * 50)
    print(f"Summary: {success_count} succeeded, {failed_count} failed")

if __name__ == "__main__":
    main()
