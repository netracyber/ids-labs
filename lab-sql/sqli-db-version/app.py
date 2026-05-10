from flask import Flask, request, render_template_string
import sqlite3
import os

app = Flask(__name__)

DB_FILE = "products.db"

# HTML Template
HOME_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Lab - Database Version Query</title>
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
            text-align: center;
            margin: 20px 0;
        }
        select, input[type="submit"] {
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
        .flag-section {
            background: #d4edda;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            display: {{ 'block' if show_flag else 'none' }};
        }
        .flag {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 18px;
            margin: 10px 0;
            word-break: break-all;
            border: 2px solid #856404;
        }
        .version-display {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            text-align: center;
            font-family: monospace;
            font-size: 16px;
            display: {{ 'block' if show_version else 'none' }};
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SQL Injection Lab - Database Version Query</h1>

        <div class="nav">
            <a href="/">Home</a>
        </div>

        <div class="instructions">
            <h3>Lab Instructions</h3>
            <p>This application contains a SQL injection vulnerability in the product category filter.</p>
            <p>You can use a UNION attack to retrieve the results from an injected query.</p>
            <p>Your goal is to display the database version string.</p>
            <p>For MySQL: Try using UNION SELECT @@version or VERSION()</p>
            <p>For Microsoft SQL Server: Try using UNION SELECT @@version or VERSION()</p>
            <p>Example payload: <code>Gifts' UNION SELECT NULL, 'Version', 'Description', 0, 'Category', 1 --</code></p>
        </div>

        <div class="section">
            <h2>Filter Products by Category</h2>
            <form method="GET" action="/">
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Electronics" {{ 'selected' if selected_category == 'Electronics' else '' }}>Electronics</option>
                    <option value="Home" {{ 'selected' if selected_category == 'Home' else '' }}>Home</option>
                    <option value="Gifts" {{ 'selected' if selected_category == 'Gifts' else '' }}>Gifts</option>
                    <option value="Books" {{ 'selected' if selected_category == 'Books' else '' }}>Books</option>
                </select>
                <input type="submit" value="Filter">
            </form>
        </div>

        <div class="version-display">
            <h3>Database Version:</h3>
            <p>{{ version_info }}</p>
        </div>

        <div class="section">
            <h2>Products</h2>
            {% if products %}
                {% for product in products %}
                    <div class="product">
                        <h3>{{ product[1] }}</h3>
                        <p>{{ product[2] }}</p>
                        <p><strong>Price:</strong> ${{ "%.2f"|format(product[3]) }}</p>
                        <p><strong>Category:</strong> {{ product[4] }}</p>
                        <p><strong>Released:</strong> 
                            {% if product[5] == 1 %}
                                <span style="color: green;">Yes</span>
                            {% else %}
                                <span style="color: red;">No</span>
                            {% endif %}
                        </p>
                    </div>
                {% endfor %}
            {% else %}
                <p>No products found in this category.</p>
            {% endif %}
        </div>

        <div class="flag-section">
            <h2>Congratulations!</h2>
            <p>You've successfully retrieved the database version!</p>
            <div class="flag">IDS{4c24a70d8e6436cb7bc3c986d54d7723}</div>
            <p>Challenge completed. You have demonstrated how SQL injection can be used to extract database information.</p>
        </div>
    </div>
    
    <script>
        // Check if database version is displayed
        const versionElement = document.querySelector('.version-display');
        if (versionElement && versionElement.textContent.trim() !== '') {
            document.querySelector('.flag-section').style.display = 'block';
        }
    </script>
</body>
</html>
"""

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
            category TEXT,
            released INTEGER DEFAULT 1
        )
    ''')

    # Insert sample data if table is empty
    cur.execute("SELECT COUNT(*) FROM products")
    if cur.fetchone()[0] == 0:
        sample_products = [
            ("Laptop", "High performance laptop", 1200.00, "Electronics", 1),
            ("Smartphone", "Latest model smartphone", 800.00, "Electronics", 1),
            ("Coffee Mug", "Ceramic coffee mug", 15.00, "Home", 1),
            ("Gift Card", "Generic gift card", 25.00, "Gifts", 1),
            ("Programming Book", "Learn SQL injection", 40.00, "Books", 1),
        ]
        cur.executemany("INSERT INTO products (name, description, price, category, released) VALUES (?, ?, ?, ?, ?)", sample_products)

    conn.commit()
    conn.close()

def query_db(query):
    conn = sqlite3.connect(DB_FILE)
    cur = conn.cursor()
    try:
        result = cur.execute(query).fetchall()
    except sqlite3.Error as e:
        # If there's an error, return empty result
        result = []
    conn.close()
    return result

@app.route("/")
def home():
    category = request.args.get('category', '')
    
    if category:
        # VULNERABLE QUERY - SQL injection in WHERE clause with UNION possibility
        # The query is: SELECT * FROM products WHERE category = 'Gifts'
        sql_query = f"SELECT * FROM products WHERE category = '{category}'"
        products = query_db(sql_query)
        
        # Check if the query result contains database version information
        # This is a simplified check - in a real scenario, we'd look for version strings
        version_info = ""
        show_version = False
        
        # Check if the category parameter contains version-related keywords
        # This simulates detecting if a UNION query was successful
        if 'version' in category.lower() or '@@version' in category.lower():
            version_info = "Database Version: Simulated DB Version 1.0.0"
            show_version = True
        elif 'union' in category.lower() and 'version' in category.lower():
            version_info = "Database Version: MySQL 8.0.28 or SQL Server 2019"
            show_version = True
        else:
            show_version = False
            version_info = ""
    else:
        # Show all products if no category selected
        sql_query = "SELECT * FROM products"
        products = query_db(sql_query)
        show_version = False
        version_info = ""
    
    # Check if any product name contains version-like strings (simulating UNION results)
    for product in products:
        if product and len(product) > 1 and 'version' in str(product[1]).lower():
            show_version = True
            version_info = str(product[1])
            break
    
    return render_template_string(
        HOME_TEMPLATE, 
        products=products, 
        selected_category=category,
        show_flag=show_version,
        show_version=show_version,
        version_info=version_info
    )

if __name__ == "__main__":
    init_db()
    app.run(host="0.0.0.0", port=5000, debug=True)