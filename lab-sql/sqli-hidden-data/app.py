from flask import Flask, request, render_template_string
import sqlite3
import secrets
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
    <title>SQL Injection Lab - Hidden Data Retrieval</title>
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
            background: #e8f4fc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            display: {{ 'block' if show_flag else 'none' }};
        }
        .flag {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 18px;
            margin: 10px 0;
            word-break: break-all;
            border: 2px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SQL Injection Lab - Hidden Data Retrieval</h1>

        <div class="nav">
            <a href="/">Home</a>
        </div>

        <div class="instructions">
            <h3>Lab Instructions</h3>
            <p>This application contains a SQL injection vulnerability in the product category filter.</p>
            <p>When you select a category, the application executes a query like:</p>
            <code>SELECT * FROM products WHERE category = 'Gifts' AND released = 1</code>
            <p>Your goal is to perform a SQL injection attack that causes the application to display one or more unreleased products.</p>
            <p>Hint: You need to manipulate the WHERE clause to bypass the "AND released = 1" condition.</p>
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
                                <span style="color: red;">No (Hidden)</span>
                            {% endif %}
                        </p>
                    </div>
                {% endfor %}
            {% else %}
                <p>No products found in this category.</p>
            {% endif %}
        </div>

        {% if show_flag %}
        <div class="flag-section">
            <h2>Congratulations!</h2>
            <p>You've successfully retrieved hidden data!</p>
            <div class="flag">{{ flag }}</div>
            <p>Challenge completed. You have demonstrated how SQL injection can be used to bypass security filters.</p>
        </div>
        {% endif %}
    </div>

    <script>
        // Check if any unreleased products are displayed
        const unreleasedElements = document.querySelectorAll('span[style*="color: red"]');
        if (unreleasedElements.length > 0) {
            const flagSection = document.querySelector('.flag-section');
            if (flagSection) {
                flagSection.style.display = 'block';
            }
        }

        // Show alert if SQL payload is detected
        {% if sql_payload_detected %}
        alert("SQL Injection payload detected! Flag: {{ flag }}");
        {% endif %}
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
            (" unreleased prototype", "Top secret device", 9999.00, "Electronics", 0),  # Hidden product
            ("Coffee Mug", "Ceramic coffee mug", 15.00, "Home", 1),
            (" unreleased home item", "New home product", 25.00, "Home", 0),  # Hidden product
            ("Gift Card", "Generic gift card", 25.00, "Gifts", 1),
            ("Special Gift", "Limited edition gift", 50.00, "Gifts", 0),  # Hidden product
            ("Programming Book", "Learn SQL injection", 40.00, "Books", 1),
            ("Secret Book", "Hidden knowledge", 60.00, "Books", 0),  # Hidden product
        ]
        cur.executemany("INSERT INTO products (name, description, price, category, released) VALUES (?, ?, ?, ?, ?)", sample_products)

    conn.commit()
    conn.close()

FLAG_FILE = "current_flag.txt"

def generate_flag():
    """Generate a random flag in the format IDS{code_acak...} and save it to file"""
    if os.path.exists(FLAG_FILE):
        with open(FLAG_FILE, 'r') as f:
            return f.read().strip()
    else:
        random_code = secrets.token_hex(16)  # 32 character hex string
        flag = f"IDS{{{random_code}}}"
        with open(FLAG_FILE, 'w') as f:
            f.write(flag)
        return flag

def query_db(query):
    conn = sqlite3.connect(DB_FILE)
    cur = conn.cursor()
    try:
        result = cur.execute(query).fetchall()
    except sqlite3.Error as e:
        result = []
    conn.close()
    return result

@app.route("/")
def home():
    category = request.args.get('category', '')
    sql_payload_detected = False

    if category:
        # Check if the input contains SQL injection patterns
        sql_injection_patterns = [
            "'", "--", "#", "/*", "*/", "UNION", "SELECT", "INSERT", "UPDATE",
            "DELETE", "DROP", "CREATE", "ALTER", "EXEC", "UNION SELECT",
            "OR 1=1", "OR '1'='1'", "OR 1=1--", "OR 1=1#", "OR 1=1/*",
            "HAVING", "GROUP BY", "ORDER BY", "LIMIT", "OFFSET", "PROCEDURE",
            "ANALYSE", "SLEEP", "BENCHMARK", "WAITFOR", "DELAY", "CAST",
            "CONVERT", "DECLARE", "OPEN", "FETCH", "CLOSE", "DEALLOCATE"
        ]

        # Check if any SQL injection pattern is detected in the input
        category_upper = category.upper()
        for pattern in sql_injection_patterns:
            if pattern.upper() in category_upper:
                sql_payload_detected = True
                break

        # VULNERABLE QUERY - SQL injection in WHERE clause
        # The query is: SELECT * FROM products WHERE category = 'Gifts' AND released = 1
        sql_query = f"SELECT * FROM products WHERE category = '{category}' AND released = 1"
        products = query_db(sql_query)
    else:
        # Show all released products if no category selected
        sql_query = "SELECT * FROM products WHERE released = 1"
        products = query_db(sql_query)

    # Check if any unreleased products are in the results (this shouldn't happen with normal queries)
    has_unreleased = any(product[5] == 0 for product in products)

    # Generate flag for display when hidden data is retrieved
    flag = generate_flag()

    return render_template_string(
        HOME_TEMPLATE,
        products=products,
        selected_category=category,
        show_flag=has_unreleased,
        sql_payload_detected=sql_payload_detected,
        flag=flag
    )

if __name__ == "__main__":
    init_db()
    app.run(host="0.0.0.0", port=5000, debug=True)