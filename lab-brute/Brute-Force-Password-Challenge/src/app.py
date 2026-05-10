from flask import Flask, render_template, request, redirect, url_for, session
import hashlib
import sys
import os

app = Flask(__name__, template_folder='templates')
app.secret_key = 'supersecretkeyforctflab'

# Simple in-memory user database
USERS = {
    'admin': '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'  # password is 'password'
}

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
        
        # Hash the provided password using SHA-256
        password_hash = hashlib.sha256(password.encode()).hexdigest()
        
        # Check if user exists and password is correct
        if username in USERS and USERS[username] == password_hash:
            session['username'] = username
            return redirect(url_for('dashboard'))
        else:
            return render_template('login.html', error='Invalid credentials')
    
    return render_template('login.html')

@app.route('/dashboard')
def dashboard():
    if 'username' in session:
        return render_template('dashboard.html', username=session['username'], flag=FLAG)
    else:
        return redirect(url_for('index'))

@app.route('/logout')
def logout():
    session.pop('username', None)
    return redirect(url_for('index'))

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=6001)
