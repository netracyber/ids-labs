from flask import Flask, render_template, request, redirect, url_for, session
import sqlite3
import os

def init_db():
    conn = sqlite3.connect('users.db')
    c = conn.cursor()
    
    # Create users table
    c.execute('''CREATE TABLE IF NOT EXISTS users
                 (id INTEGER PRIMARY KEY, username TEXT, password TEXT)''')
    
    # Insert admin user with known password
    c.execute("INSERT OR IGNORE INTO users (username, password) VALUES (?, ?)", 
              ('admin', 'admin123'))
    
    # Insert another user
    c.execute("INSERT OR IGNORE INTO users (username, password) VALUES (?, ?)", 
              ('user', 'password123'))
    
    # Insert the flag as a special user
    c.execute("INSERT OR IGNORE INTO users (username, password) VALUES (?, ?)", 
              ('flag', 'IDS{f752c901129d3e5decd54894268597c5}'))
    
    conn.commit()
    conn.close()

app = Flask(__name__, template_folder='templates')
app.secret_key = 'supersecretkeyforsqlinjectionlab'

# Initialize the database
init_db()

@app.route('/')
def index():
    return render_template('login.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']

        # VULNERABLE CODE: Direct string concatenation (SQL injection)
        conn = sqlite3.connect('users.db')
        c = conn.cursor()
        
        # This is vulnerable to SQL injection
        query = f"SELECT * FROM users WHERE username='{username}' AND password='{password}'"
        print(f"Executing query: {query}")  # For debugging
        
        c.execute(query)
        user = c.fetchone()
        conn.close()
        
        if user:
            session['username'] = user[1]  # username from the result
            return redirect(url_for('dashboard'))
        else:
            return render_template('login.html', error='Invalid credentials')
    
    return render_template('login.html')

@app.route('/dashboard')
def dashboard():
    if 'username' in session:
        return render_template('dashboard.html', username=session['username'])
    else:
        return redirect(url_for('index'))

@app.route('/logout')
def logout():
    session.pop('username', None)
    return redirect(url_for('index'))

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=6003)
