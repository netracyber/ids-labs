from flask import Flask, render_template, request, redirect, url_for, session, send_from_directory
import hashlib
import os
import sys

app = Flask(__name__, template_folder='templates')
app.secret_key = 'supersecretkeyforhashcatctflab'

# Hashed password (MD5 hash of the actual password)
# Users need to find this hash and crack it with hashcat or John the Ripper
# The actual password is "welcome123" which hashes to the value below
USER_HASH = '5858ea228cc2edf88721699b2c8638e5'  # MD5 hash of "welcome123"

# Generate dynamic flag
try:
    # Try to import from tools directory
    sys.path.append('/app/tools')
    from generate_flag import generate_random_flag
    FLAG = generate_random_flag()
except ImportError:
    # Fallback to simple flag generation
    import secrets
    FLAG = f"IDS{{{secrets.token_hex(16)}}}"

@app.route('/')
def index():
    return render_template('login.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']

        # Hash the provided password using MD5
        password_hash = hashlib.md5(password.encode()).hexdigest()

        # Check if password hash matches
        if password_hash == USER_HASH:
            session['authenticated'] = True
            return redirect(url_for('dashboard'))
        else:
            return render_template('login.html', error='Invalid credentials')

    return render_template('login.html')

@app.route('/dashboard')
def dashboard():
    if 'authenticated' in session:
        return render_template('dashboard.html', flag=FLAG)
    else:
        return redirect(url_for('index'))

@app.route('/logout')
def logout():
    session.pop('authenticated', None)
    return redirect(url_for('index'))

@app.route('/hash')
def hash_info():
    """Hidden endpoint that reveals the hash - users need to discover this"""
    # This is a hidden endpoint that might be discovered through enumeration
    if request.headers.get('X-Debug') == 'true':
        return {'hash': USER_HASH, 'algorithm': 'MD5'}
    else:
        return redirect(url_for('index'))

@app.route("/download-wordlist")
def download_wordlist():
    from flask import send_from_directory
    import os
    wordlist_path = os.path.abspath("/app/exploits/wordlist_exactly_100.txt")
    return send_from_directory(os.path.dirname(wordlist_path), os.path.basename(wordlist_path), as_attachment=True)

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=6002)
