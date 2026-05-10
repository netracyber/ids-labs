#!/usr/bin/env python3
"""
SQL Injection Lab - PostgreSQL Error-Based Injection (Type Casting & Syntax Leakage)
Vulnerable application for educational purposes
Author: IDS - CyberSec Academy
"""

import os
import sys
import random
import psycopg2
from flask import Flask, render_template, request, redirect, url_for, session, g
from psycopg2.extras import RealDictCursor

# Add parent directory to path for flag generator
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'tools'))
from generate_flag import get_existing_flag_or_generate

app = Flask(__name__)
app.secret_key = os.urandom(24)

# PostgreSQL configuration
DB_CONFIG = {
    'host': os.environ.get('DB_HOST', 'db'),
    'port': int(os.environ.get('DB_PORT', '5432')),
    'database': os.environ.get('DB_NAME', 'labdb'),
    'user': os.environ.get('DB_USER', 'labuser'),
    'password': os.environ.get('DB_PASSWORD', 'labpass')
}

# Random error hints about data types
ERROR_HINTS = [
    "Hint: Try different data types in your query...",
    "Notice: PostgreSQL is strict about type compatibility...",
    "ERROR: could not determine type of string vs boolean...",
    "HINT: Certain type conversions might reveal hidden data...",
]

def get_db_conn():
    """Get PostgreSQL database connection"""
    conn = getattr(g, '_db_conn', None)
    if conn is None:
        conn = g._db_conn = psycopg2.connect(
            host=DB_CONFIG['host'],
            port=DB_CONFIG['port'],
            database=DB_CONFIG['database'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            cursor_factory=RealDictCursor
        )
    return conn

@app.teardown_appcontext
def close_db_connection(exception):
    """Close database connection"""
    conn = getattr(g, '_db_conn', None)
    if conn is not None:
        conn.close()

def init_db():
    """Initialize database with vulnerable schema"""
    conn = get_db_conn()
    cursor = conn.cursor()

    # Create products table
    cursor.execute('''
        DROP TABLE IF EXISTS products;
        CREATE TABLE products (
            id SERIAL PRIMARY KEY,
            name TEXT NOT NULL,
            description TEXT,
            price TEXT,
            category TEXT
        );
    ''')

    # Create secret_admin table (for flag)
    cursor.execute('''
        DROP TABLE IF EXISTS secret_admin;
        CREATE TABLE secret_admin (
            id SERIAL PRIMARY KEY,
            username TEXT UNIQUE NOT NULL,
            password TEXT,
            role TEXT,
            flag TEXT
        );
    ''')

    # Insert sample products
    cursor.execute('''
        INSERT INTO products (id, name, description, price, category) VALUES
        (1, 'Gaming Laptop', 'High performance laptop for gaming', '1299.99', 'Electronics'),
        (2, 'Wireless Mouse', 'Ergonomic wireless mouse', '29.99', 'Electronics'),
        (3, 'Mechanical Keyboard', 'Clicky keyboard with RGB', '89.99', 'Electronics'),
        (4, 'Coffee Maker', 'Programmable coffee maker', '149.99', 'Appliances');
    ''')

    # Create admin user with flag
    cursor.execute('SELECT * FROM secret_admin WHERE username = %s', ('admin',))
    if not cursor.fetchone():
        flag = get_existing_flag_or_generate(os.path.join(os.path.dirname(__file__), '..', 'tools', 'current_flag.txt'))
        cursor.execute('INSERT INTO secret_admin (username, password, role, flag) VALUES (%s, %s, %s, %s)',
            ('admin', 'PostgreSQL@dm1nR00t!', 'administrator', flag))

    conn.commit()
    cursor.close()
    print("Database initialized successfully!")

@app.route('/')
def index():
    """Home page with product lookup"""
    # Random hint on each visit
    random_hint = random.choice(ERROR_HINTS) if random.random() > 0.4 else None
    return render_template('index.html', random_hint=random_hint)

@app.route('/lookup')
def lookup():
    """
    VULNERABLE LOOKUP FUNCTION
    PostgreSQL Error-Based Injection
    Type casting and syntax leakage through error messages
    """
    product_id = request.args.get('id', '')

    if not product_id:
        random_hint = random.choice(ERROR_HINTS)
        return render_template('index.html', error='Please provide a product ID', random_hint=random_hint)

    conn = get_db_conn()

    try:
        # VULNERABLE QUERY: Direct parameter interpolation with type casting
        # This allows error-based data extraction
        query = f"SELECT * FROM products WHERE id = {product_id}"

        print(f"[DEBUG] Query: {query}")

        cursor = conn.cursor()
        cursor.execute(query)
        result = cursor.fetchone()

        if result:
            random_hint = random.choice(ERROR_HINTS) if random.random() > 0.6 else None
            return render_template('index.html',
                product=result,
                product_id=product_id,
                query_debug=query,
                random_hint=random_hint)
        else:
            random_hint = random.choice(ERROR_HINTS)
            return render_template('index.html',
                error='Product not found',
                product_id=product_id,
                query_debug=query,
                random_hint=random_hint)

    except Exception as e:
        # IMPORTANT: Verbose error messages are shown (this is the vulnerability!)
        error_msg = str(e)
        print(f"[PG ERROR] {error_msg}")

        random_hint = random.choice(ERROR_HINTS)

        # Return error with details for exploitation
        return render_template('index.html',
            error=error_msg,
            product_id=product_id,
            query_debug=f"SELECT * FROM products WHERE id = {product_id}",
            random_hint=random_hint)

@app.route('/admin')
def admin():
    """Admin panel - accessible only with extracted credentials"""
    if 'logged_in' not in session:
        return render_template('admin.html', show_form=True)

    # Show flag if logged in
    conn = get_db_conn()
    cursor = conn.cursor()
    cursor.execute('SELECT flag FROM secret_admin WHERE username = %s', (session.get('username'),))
    result = cursor.fetchone()

    if result:
        return render_template('flag.html', flag=result['flag'], username=session.get('username'))

    return render_template('admin.html', show_form=True, error='Invalid session')

@app.route('/login', methods=['POST'])
def login():
    """Admin login - requires credentials extracted via SQLi"""
    username = request.form.get('username', '')
    password = request.form.get('password', '')

    conn = get_db_conn()
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM secret_admin WHERE username = %s AND password = %s', (username, password))
    user = cursor.fetchone()

    if user:
        session['logged_in'] = True
        session['username'] = username
        return redirect(url_for('admin'))

    return render_template('admin.html', show_form=True, error='Invalid credentials')

@app.route('/logout')
def logout():
    """Logout"""
    session.clear()
    return redirect(url_for('index'))

@app.route('/source')
def source():
    """Show source code as hint"""
    return render_template('source.html')

if __name__ == '__main__':
    # Initialize database on startup
    with app.app_context():
        init_db()
    app.run(host='0.0.0.0', port=8080, debug=True)
