from flask import Flask, request, render_template_string, redirect, url_for
import sqlite3

app = Flask(__name__)

DB_FILE = "products.db"

# -------------------------
# FLAG ENGINE
# -------------------------

def get_flag():
    # Return static flag as specified
    return "IDS{fd8840b063ec2c78cf9cdc7dec52f926}"

# -------------------------
# DATABASE
# -------------------------

def init_db():
    conn = sqlite3.connect(DB_FILE)
    cur = conn.cursor()
    
    # Create products table
    cur.execute('''
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT
        )
    ''')
    
    # Create users table
    cur.execute('''
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT NOT NULL,
            profile TEXT
        )
    ''')
    
    # Insert sample data if table is empty
    cur.execute("SELECT COUNT(*) FROM products")
    if cur.fetchone()[0] == 0:
        sample_products = [
            ("Laptop", "High performance laptop", 1200.00, "Electronics"),
            ("Smartphone", "Latest model smartphone", 800.00, "Electronics"),
            ("Coffee Mug", "Ceramic coffee mug", 15.00, "Home"),
            ("Book", "Programming guide", 35.00, "Education"),
            ("Headphones", "Wireless headphones", 150.00, "Electronics")
        ]
        cur.executemany("INSERT INTO products (name, description, price, category) VALUES (?, ?, ?, ?)", sample_products)
    
    # Insert sample users
    cur.execute("SELECT COUNT(*) FROM users")
    if cur.fetchone()[0] == 0:
        sample_users = [
            ("admin", "admin@example.com", "Administrator account"),
            ("john_doe", "john@example.com", "Regular user"),
            ("jane_smith", "jane@example.com", "Regular user")
        ]
        cur.executemany("INSERT INTO users (username, email, profile) VALUES (?, ?, ?)", sample_users)
    
    conn.commit()
    conn.close()

def query_db(query):
    conn = sqlite3.connect(DB_FILE)
    cur = conn.cursor()
    try:
        result = cur.execute(query).fetchall()
    except sqlite3.Error as e:
        result = []
    conn.close()
    return result

def query_single_db(query):
    conn = sqlite3.connect(DB_FILE)
    cur = conn.cursor()
    try:
        result = cur.execute(query).fetchone()
    except sqlite3.Error as e:
        result = None
    conn.close()
    return result

# -------------------------
# HTML TEMPLATES
# -------------------------

HOME_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection CTF Lab - Other Endpoints</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .nav {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #2980b9;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .section h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        form {
            margin: 15px 0;
        }
        input[type="text"], input[type="submit"] {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background: #3498db;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #2980b9;
        }
        .product {
            border: 1px solid #eee;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: white;
        }
        .instructions {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SQL Injection CTF Lab - Other Endpoints</h1>
        
        <div class="nav">
            <a href="/">Home</a>
            <a href="/search">Search Products</a>
            <a href="/user">User Profile</a>
            <a href="/category">Browse by Category</a>
        </div>
        
        <div class="instructions">
            <h3>CTF Challenge Instructions</h3>
            <p>This application contains multiple SQL injection vulnerabilities in different endpoints:</p>
            <ul>
                <li><strong>Search Products</strong>: Vulnerable to SQL injection in the search parameter</li>
                <li><strong>User Profile</strong>: Vulnerable to SQL injection in the user ID parameter</li>
                <li><strong>Browse by Category</strong>: Vulnerable to SQL injection in the category parameter</li>
            </ul>
            <p>Find and exploit these vulnerabilities to extract sensitive information and get the flag!</p>
        </div>
        
        <div class="section">
            <h2>Available Endpoints</h2>
            <p>Click on the navigation links above to explore different vulnerable endpoints.</p>
            <p>Each endpoint has its own SQL injection vulnerability that you need to exploit.</p>
        </div>
        
        <div class="section">
            <h2>About This Challenge</h2>
            <p>This is a SQL injection challenge with dynamic flags that rotate every hour.</p>
            <p>Successfully exploiting any of the vulnerabilities will reveal the current flag.</p>
        </div>
    </div>
</body>
</html>
"""

SEARCH_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Products - SQL Injection Lab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .nav {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #2980b9;
        }
        form {
            text-align: center;
            margin: 20px 0;
        }
        input[type="text"] {
            width: 300px;
            padding: 12px;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background: #2980b9;
        }
        .product {
            border: 1px solid #eee;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: white;
        }
        .instructions {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Search Products</h1>
        
        <div class="nav">
            <a href="/">Home</a>
            <a href="/search">Search Products</a>
            <a href="/user">User Profile</a>
            <a href="/category">Browse by Category</a>
        </div>
        
        <div class="instructions">
            <h3>SQL Injection Challenge</h3>
            <p>This search functionality is vulnerable to SQL injection. Try searching with special characters like ' or using SQL injection payloads.</p>
            <p>Example payloads: <code>' OR '1'='1</code>, <code>' UNION SELECT 1,2,3,4 --</code></p>
        </div>
        
        <form method="GET" action="/search">
            <input type="text" name="q" placeholder="Search products..." value="{{ query or '' }}">
            <input type="submit" value="Search">
        </form>
        
        {% if products %}
            <h2>Search Results for "{{ query }}"</h2>
            {% for product in products %}
                <div class="product">
                    <h3>{{ product[1] }}</h3>
                    <p>{{ product[2] }}</p>
                    <p><strong>Price:</strong> ${{ "%.2f"|format(product[3]) }}</p>
                    <p><strong>Category:</strong> {{ product[4] }}</p>
                </div>
            {% endfor %}
        {% elif query %}
            <p>No products found for "{{ query }}". Try a different search term or SQL injection payload.</p>
        {% endif %}
    </div>
</body>
</html>
"""

USER_PROFILE_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - SQL Injection Lab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .nav {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #2980b9;
        }
        .profile {
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 8px;
            background: white;
            margin: 20px 0;
        }
        .instructions {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
        }
        form {
            text-align: center;
            margin: 20px 0;
        }
        input[type="text"] {
            width: 200px;
            padding: 12px;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>
        
        <div class="nav">
            <a href="/">Home</a>
            <a href="/search">Search Products</a>
            <a href="/user">User Profile</a>
            <a href="/category">Browse by Category</a>
        </div>
        
        <div class="instructions">
            <h3>SQL Injection Challenge</h3>
            <p>This user profile functionality is vulnerable to SQL injection through the user ID parameter.</p>
            <p>Try manipulating the user ID parameter with SQL injection payloads.</p>
            <p>Example payloads: <code>1 OR 1=1</code>, <code>1 UNION SELECT 1,2,3 --</code></p>
        </div>
        
        <form method="GET" action="/user">
            <label for="id">User ID:</label>
            <input type="text" name="id" placeholder="Enter user ID" value="{{ user_id or '' }}">
            <input type="submit" value="View Profile">
        </form>
        
        {% if user %}
            <div class="profile">
                <h2>User Profile</h2>
                <p><strong>ID:</strong> {{ user[0] }}</p>
                <p><strong>Username:</strong> {{ user[1] }}</p>
                <p><strong>Email:</strong> {{ user[2] }}</p>
                <p><strong>Profile:</strong> {{ user[3] }}</p>
            </div>
        {% elif user_id %}
            <p>No user found with ID "{{ user_id }}". Try a different ID or SQL injection payload.</p>
        {% endif %}
    </div>
</body>
</html>
"""

CATEGORY_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse by Category - SQL Injection Lab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .nav {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #2980b9;
        }
        .product {
            border: 1px solid #eee;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: white;
        }
        .instructions {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
        }
        form {
            text-align: center;
            margin: 20px 0;
        }
        input[type="text"] {
            width: 200px;
            padding: 12px;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Browse by Category</h1>
        
        <div class="nav">
            <a href="/">Home</a>
            <a href="/search">Search Products</a>
            <a href="/user">User Profile</a>
            <a href="/category">Browse by Category</a>
        </div>
        
        <div class="instructions">
            <h3>SQL Injection Challenge</h3>
            <p>This category browsing functionality is vulnerable to SQL injection through the category parameter.</p>
            <p>Try manipulating the category parameter with SQL injection payloads.</p>
            <p>Example payloads: <code>Electronics' OR '1'='1</code>, <code>Electronics' UNION SELECT 1,2,3,4 --</code></p>
        </div>
        
        <form method="GET" action="/category">
            <label for="cat">Category:</label>
            <input type="text" name="cat" placeholder="Enter category" value="{{ category or '' }}">
            <input type="submit" value="Browse">
        </form>
        
        {% if products %}
            <h2>Products in Category "{{ category }}"</h2>
            {% for product in products %}
                <div class="product">
                    <h3>{{ product[1] }}</h3>
                    <p>{{ product[2] }}</p>
                    <p><strong>Price:</strong> ${{ "%.2f"|format(product[3]) }}</p>
                    <p><strong>Category:</strong> {{ product[4] }}</p>
                </div>
            {% endfor %}
        {% elif category %}
            <p>No products found in category "{{ category }}". Try a different category or SQL injection payload.</p>
        {% endif %}
    </div>
</body>
</html>
"""

FLAG_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge Completed - SQL Injection Lab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 600px;
        }
        h1 {
            color: #27ae60;
            margin-bottom: 20px;
        }
        .flag {
            background: #e8f4fc;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 18px;
            margin: 20px 0;
            word-break: break-all;
        }
        .nav {
            margin-top: 30px;
        }
        .nav a {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Congratulations!</h1>
        <p>You've successfully exploited the SQL injection vulnerability.</p>
        <p>Here is your flag:</p>
        <div class="flag">{{ flag }}</div>
        <p>This is the static flag for this challenge.</p>
        <div class="nav">
            <a href="/">Return to Home</a>
        </div>
    </div>
</body>
</html>
"""

# -------------------------
# ROUTES
# -------------------------

@app.route("/")
def home():
    return render_template_string(HOME_TEMPLATE)

@app.route("/search")
def search():
    query = request.args.get('q', '')
    
    if query:
        # VULNERABLE QUERY - Search functionality
        sql_query = f"SELECT * FROM products WHERE name LIKE '%{query}%' OR description LIKE '%{query}%'"
        products = query_db(sql_query)
        
        # Check if the query was manipulated to extract flag
        if "flag" in query.lower() or "current_flag" in query.lower():
            flag = get_flag()
            return render_template_string(FLAG_TEMPLATE, flag=flag)
    else:
        products = []
    
    return render_template_string(SEARCH_TEMPLATE, products=products, query=query)

@app.route("/user")
def user_profile():
    user_id = request.args.get('id', '')
    
    if user_id:
        # VULNERABLE QUERY - User profile by ID
        sql_query = f"SELECT * FROM users WHERE id={user_id}"
        user = query_single_db(sql_query)
        
        # Check if the query was manipulated to extract flag
        if "flag" in user_id.lower() or "current_flag" in user_id.lower():
            flag = get_flag()
            return render_template_string(FLAG_TEMPLATE, flag=flag)
    else:
        user = None
    
    return render_template_string(USER_PROFILE_TEMPLATE, user=user, user_id=user_id)

@app.route("/category")
def category():
    category = request.args.get('cat', '')
    
    if category:
        # VULNERABLE QUERY - Category browsing
        sql_query = f"SELECT * FROM products WHERE category='{category}'"
        products = query_db(sql_query)
        
        # Check if the query was manipulated to extract flag
        if "flag" in category.lower() or "current_flag" in category.lower():
            flag = get_flag()
            return render_template_string(FLAG_TEMPLATE, flag=flag)
    else:
        products = []
    
    return render_template_string(CATEGORY_TEMPLATE, products=products, category=category)

if __name__ == "__main__":
    init_db()
    app.run(host="0.0.0.0", port=5000, debug=True)