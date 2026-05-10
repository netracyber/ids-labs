from flask import Flask, request, render_template_string
import sqlite3
import os

app = Flask(__name__)

DB_FILE = "products.db"

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
    
    # Insert sample data
    sample_products = [
        ("Laptop", "High performance laptop", 1200.00, "Electronics"),
        ("Smartphone", "Latest model smartphone", 800.00, "Electronics"),
        ("Coffee Mug", "Ceramic coffee mug", 15.00, "Home"),
        ("Book", "Programming guide", 35.00, "Education"),
        ("Headphones", "Wireless headphones", 150.00, "Electronics")
    ]
    cur.executemany("INSERT OR REPLACE INTO products (id, name, description, price, category) VALUES (NULL, ?, ?, ?, ?)", sample_products)
    
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

# HTML template for the application
HOME_TEMPLATE = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Store - SQL Injection Lab</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .filter-form {
            margin: 20px 0;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            background: #fafafa;
        }
        .challenge-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Product Store</h1>
        
        <div class="challenge-info">
            <h3>SQL Injection Challenge</h3>
            <p>This lab contains a SQL injection vulnerability in the product category filter.</p>
            <p>You can use a UNION attack to retrieve the results from an injected query.</p>
            <p><strong>To solve the lab:</strong> Display the database version string.</p>
        </div>
        
        <form class="filter-form" method="GET" action="/filter">
            <label for="category">Filter by Category:</label><br>
            <input type="text" id="category" name="category" placeholder="Enter category (e.g., Electronics)" value="{{ category or '' }}">
            <input type="submit" value="Filter">
        </form>
        
        {% if products %}
            <div class="products">
                {% for product in products %}
                    <div class="product">
                        <h3>{{ product[1] }}</h3>
                        <p>{{ product[2] }}</p>
                        <p><strong>Price:</strong> ${{ "%.2f"|format(product[3]) }}</p>
                        <p><strong>Category:</strong> {{ product[4] }}</p>
                    </div>
                {% endfor %}
            </div>
        {% elif category %}
            <p>No products found for category: "{{ category }}". Try a different category or SQL injection payload.</p>
        {% endif %}
    </div>
</body>
</html>
"""

@app.route("/")
def home():
    return render_template_string(HOME_TEMPLATE)

@app.route("/filter")
def filter_products():
    category = request.args.get('category', '')
    
    if category:
        # VULNERABLE QUERY - Product category filter
        sql_query = f"SELECT * FROM products WHERE category='{category}'"
        products = query_db(sql_query)
        
        # Check if the query was manipulated to extract database version
        if "version" in category.lower() or "banner" in category.lower():
            # Simulate Oracle database version response
            version_result = query_db("SELECT 'Oracle Database 19c Release 19.0.0.0.0' as version")
            if version_result:
                # Create a special response to show the version
                special_template = """
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Database Version</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 20px;
                            background-color: #f5f5f5;
                        }
                        .container {
                            max-width: 600px;
                            margin: 0 auto;
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                            text-align: center;
                        }
                        .success {
                            background: #d4edda;
                            color: #155724;
                            padding: 15px;
                            border-radius: 4px;
                            margin: 20px 0;
                            border: 1px solid #c3e6cb;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Database Version</h1>
                        <div class="success">
                            <h2>Congratulations!</h2>
                            <p>You have successfully retrieved the database version:</p>
                            <p><strong>Oracle Database 19c Release 19.0.0.0.0</strong></p>
                        </div>
                        <a href="/">Back to Store</a>
                    </div>
                </body>
                </html>
                """
                return render_template_string(special_template)
    else:
        products = []
    
    return render_template_string(HOME_TEMPLATE, products=products, category=category)

if __name__ == "__main__":
    init_db()
    app.run(host="0.0.0.0", port=5005, debug=True)