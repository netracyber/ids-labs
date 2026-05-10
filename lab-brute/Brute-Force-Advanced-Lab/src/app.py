from flask import Flask, render_template, request, redirect, url_for, session, jsonify
import hashlib
import time
import threading
from collections import defaultdict
import sys

app = Flask(__name__, template_folder='templates')
app.secret_key = 'supersecretkeyforbruteforcelab'

# Dictionary to track failed attempts per IP
failed_attempts = defaultdict(list)

# Correct credentials
CORRECT_USERNAME = 'admin'
CORRECT_PASSWORD = 'securepass123'  # The password to brute force

# Rate limiting: Max 3 attempts per 10 seconds per IP
MAX_ATTEMPTS = 3
TIME_WINDOW = 10

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
    client_ip = request.remote_addr
    current_time = time.time()
    
    # Clean old attempts outside the time window
    failed_attempts[client_ip] = [attempt_time for attempt_time in failed_attempts[client_ip] 
                                  if current_time - attempt_time < TIME_WINDOW]
    
    # Check if rate limited
    if len(failed_attempts[client_ip]) >= MAX_ATTEMPTS:
        return render_template('login.html', error=f'Max attempts exceeded. Try again later.')
    
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        # Add delay to slow down brute force attempts
        time.sleep(0.5)
        
        if username == CORRECT_USERNAME and password == CORRECT_PASSWORD:
            session['authenticated'] = True
            session['username'] = username
            # Reset attempts on success
            failed_attempts[client_ip] = []
            return redirect(url_for('dashboard'))
        else:
            # Record failed attempt
            failed_attempts[client_ip].append(current_time)
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
    session.pop('username', None)
    return redirect(url_for('index'))

@app.route('/api/status')
def api_status():
    """API endpoint that might leak information for advanced techniques"""
    client_ip = request.remote_addr
    attempts_count = len([t for t in failed_attempts[client_ip] if time.time() - t < TIME_WINDOW])
    return jsonify({
        'attempts_remaining': MAX_ATTEMPTS - attempts_count,
        'time_window_seconds': TIME_WINDOW,
        'rate_limited': attempts_count >= MAX_ATTEMPTS
    })

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=6004)
