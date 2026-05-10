from flask import Flask, request, render_template_string, redirect, url_for, session
import cx_Oracle
import os

app = Flask(__name__)
app.secret_key = 'supersecretkey'

# Database connection configuration
DB_HOST = os.environ.get('DB_HOST', 'localhost')
DB_PORT = os.environ.get('DB_PORT', '1521')
DB_NAME = os.environ.get('DB_NAME', 'XE')
DB_USER = os.environ.get('DB_USER', 'system')
DB_PASSWORD = os.environ.get('DB_PASSWORD', 'oracle')

def get_db_connection():
    dsn = cx_Oracle.makedsn(DB_HOST, DB_PORT, service_name=DB_NAME)
    connection = cx_Oracle.connect(user=DB_USER, password=DB_PASSWORD, dsn=dsn)
    return connection

def init_db():
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # Create categories table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS categories (
            id NUMBER PRIMARY KEY,
            name VARCHAR2(100) NOT NULL
        )
    """)
    
    # Create products table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS products (
            id NUMBER PRIMARY KEY,
            name VARCHAR2(100) NOT NULL,
            description VARCHAR2(500),
            price NUMBER(10,2),
            category_id NUMBER,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    """)
    
    # Create users table (this is what we need to find through SQL injection)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS user_accounts (
            id NUMBER PRIMARY KEY,
            username VARCHAR2(50) NOT NULL,
            password VARCHAR2(100) NOT NULL
        )
    """)
    
    # Insert sample data
    try:
        cursor.execute("INSERT INTO categories VALUES (1, 'Electronics')")
        cursor.execute("INSERT INTO categories VALUES (2, 'Books')")
        cursor.execute("INSERT INTO categories VALUES (3, 'Clothing')")
        
        cursor.execute("INSERT INTO products VALUES (1, 'Laptop', 'High-performance laptop', 999.99, 1)")
        cursor.execute("INSERT INTO products VALUES (2, 'Smartphone', 'Latest model smartphone', 699.99, 1)")
        cursor.execute("INSERT INTO products VALUES (3, 'Python Guide', 'Learn Python programming', 29.99, 2)")
        cursor.execute("INSERT INTO products VALUES (4, 'T-Shirt', 'Cotton t-shirt', 19.99, 3)")
        
        # Insert admin user
        cursor.execute("INSERT INTO user_accounts VALUES (1, 'administrator', 's3cr3t_p@ssw0rd')")
        cursor.execute("INSERT INTO user_accounts VALUES (2, 'user1', 'password123')")
        cursor.execute("INSERT INTO user_accounts VALUES (3, 'testuser', 'testpass')")
        
        conn.commit()
    except cx_Oracle.IntegrityError:
        # Data already exists
        pass
    
    cursor.close()
    conn.close()

@app.route('/')
def index():
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # Vulnerable SQL query - directly concatenating user input
    category = request.args.get('category', 'Electronics')
    
    # This is the vulnerable query - SQL injection point
    query = f"SELECT name, description, price FROM products WHERE category_id = (SELECT id FROM categories WHERE name = '{category}')"
    
    try:
        cursor.execute(query)
        products = cursor.fetchall()
    except Exception as e:
        # If the query fails, show all products
        cursor.execute("SELECT name, description, price FROM products")
        products = cursor.fetchall()
    
    # Get all categories for the filter
    cursor.execute("SELECT name FROM categories")
    categories = [row[0] for row in cursor.fetchall()]
    
    cursor.close()
    conn.close()
    
    html = '''
    <!DOCTYPE html>
    <html>
    <head>
        <title>SQL Injection Lab - Oracle</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .product { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
            .categories { margin-bottom: 20px; }
            .categories a { margin-right: 10px; text-decoration: none; color: #007bff; }
            .login-form { margin-top: 30px; padding: 20px; border: 1px solid #ddd; }
            input[type="text"], input[type="password"] { padding: 8px; margin: 5px; width: 200px; }
            input[type="submit"] { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>SQL Injection Lab - Oracle Database</h1>
        <p>Current category filter: <strong>{}</strong></p>
        
        <div class="categories">
            <strong>Filter by category:</strong>
            {}
        </div>
        
        <h2>Products</h2>
        {}
        
        <div class="login-form">
            <h3>Login</h3>
            <form method="POST" action="/login">
                <div>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div>
                    <input type="submit" value="Login">
                </div>
            </form>
            {}
        </div>
    </body>
    </html>
    '''
    
    # Format products HTML
    products_html = ""
    if products:
        for product in products:
            products_html += f'<div class="product"><h3>{product[0]}</h3><p>{product[1]}</p><p>Price: ${product[2]}</p></div>'
    else:
        products_html = '<p>No products found in this category.</p>'
    
    # Format category links
    category_links = ""
    for cat in categories:
        category_links += f'<a href="/?category={cat}">{cat}</a>'
    
    # Show login status
    login_status = ""
    if 'username' in session:
        login_status = f'<p>Logged in as: <strong>{session["username"]}</strong> <a href="/logout">Logout</a></p>'
    
    return render_template_string(html, category, category_links, products_html, login_status)

@app.route('/login', methods=['POST'])
def login():
    username = request.form['username']
    password = request.form['password']
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # Safe login query (not vulnerable)
    query = "SELECT username FROM user_accounts WHERE username = :username AND password = :password"
    cursor.execute(query, {'username': username, 'password': password})
    user = cursor.fetchone()
    
    cursor.close()
    conn.close()
    
    if user:
        session['username'] = user[0]
        return redirect(url_for('index'))
    else:
        return redirect(url_for('index'))

@app.route('/logout')
def logout():
    session.pop('username', None)
    return redirect(url_for('index'))

if __name__ == '__main__':
    init_db()
    app.run(host='0.0.0.0', port=5000, debug=True)