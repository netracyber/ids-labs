from flask import Flask, request
import sqlite3
import os

app = Flask(__name__)

DB_FILE = "users.db"
STATIC_FLAG = "IDS{ba529b67b293c336dc30db18913c7020}"

# -------------------------
# FLAG ENGINE
# -------------------------

def get_flag():
    return STATIC_FLAG

# -------------------------
# DATABASE
# -------------------------

def query_db(query):
    conn = sqlite3.connect(DB_FILE)
    cur = conn.cursor()
    result = cur.execute(query).fetchone()
    conn.close()
    return result

# -------------------------
# ROUTES
# -------------------------

@app.route("/", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        username = request.form.get("username", "")
        password = request.form.get("password", "")

        # VULNERABLE QUERY
        query = f"SELECT * FROM users WHERE username='{username}' AND password='{password}'"

        if query_db(query):
            flag = get_flag()
            return f"""
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Challenge Completed</title>
                <style>
                    body {{
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                        min-height: 100vh;
                        margin: 0;
                        padding: 20px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        color: #333;
                    }}
                    .container {{
                        background: white;
                        border-radius: 16px;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                        padding: 40px;
                        max-width: 500px;
                        width: 100%;
                        text-align: center;
                    }}
                    .success-icon {{
                        width: 80px;
                        height: 80px;
                        background: #4BB543;
                        border-radius: 50%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        margin: 0 auto 20px;
                        color: white;
                        font-size: 32px;
                    }}
                    h1 {{
                        color: #2c3e50;
                        margin-bottom: 10px;
                    }}
                    .flag {{
                        background: #e3f2fd;
                        padding: 15px;
                        border-radius: 8px;
                        margin: 20px 0;
                        font-family: monospace;
                        font-size: 18px;
                        word-break: break-all;
                        border: 1px solid #bbdefb;
                    }}
                    .note {{
                        background: #fff8e1;
                        padding: 15px;
                        border-radius: 8px;
                        margin-top: 20px;
                        font-size: 14px;
                        color: #5d4037;
                        border-left: 4px solid #ffc107;
                    }}
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="success-icon">✓</div>
                    <h1>Authentication Successful</h1>
                    <p>Congratulations! You've successfully bypassed the authentication system.</p>
                    <div class="flag">
                        {flag}
                    </div>
                </div>
            </body>
            </html>
            """
        else:
            return """
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Login - SQL Injection Lab</title>
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
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                        padding: 40px;
                        max-width: 500px;
                        width: 100%;
                        text-align: center;
                    }
                    h2 {
                        color: #2c3e50;
                        margin-bottom: 30px;
                    }
                    .form-group {
                        margin-bottom: 20px;
                        text-align: left;
                    }
                    label {
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 600;
                        color: #555;
                    }
                    input[type="text"], input[type="password"] {
                        width: 100%;
                        padding: 14px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        font-size: 16px;
                        box-sizing: border-box;
                        transition: border-color 0.3s;
                    }
                    input[type="text"]:focus, input[type="password"]:focus {
                        border-color: #3498db;
                        outline: none;
                        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
                    }
                    button {
                        background: #3498db;
                        color: white;
                        border: none;
                        padding: 14px 20px;
                        width: 100%;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: background 0.3s;
                    }
                    button:hover {
                        background: #2980b9;
                    }
                    .title {
                        font-size: 24px;
                        margin-bottom: 5px;
                        color: #2c3e50;
                    }
                    .subtitle {
                        color: #7f8c8d;
                        margin-bottom: 25px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h2 class="title">Secure Login Portal</h2>
                    <p class="subtitle">SQL Injection Challenge</p>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter your username">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password">
                        </div>
                        <button type="submit">Login</button>
                    </form>
                </div>
            </body>
            </html>
            """

    return """
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - SQL Injection Lab</title>
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
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                padding: 40px;
                max-width: 500px;
                width: 100%;
                text-align: center;
            }
            h2 {
                color: #2c3e50;
                margin-bottom: 30px;
            }
            .form-group {
                margin-bottom: 20px;
                text-align: left;
            }
            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #555;
            }
            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 14px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 16px;
                box-sizing: border-box;
                transition: border-color 0.3s;
            }
            input[type="text"]:focus, input[type="password"]:focus {
                border-color: #3498db;
                outline: none;
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            }
            button {
                background: #3498db;
                color: white;
                border: none;
                padding: 14px 20px;
                width: 100%;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover {
                background: #2980b9;
            }
            .title {
                font-size: 24px;
                margin-bottom: 5px;
                color: #2c3e50;
            }
            .subtitle {
                color: #7f8c8d;
                margin-bottom: 25px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2 class="title">Secure Login Portal</h2>
            <p class="subtitle">SQL Injection Challenge</p>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    """

# -------------------------
# INIT DATABASE
# -------------------------

def init_db():
    if not os.path.exists(DB_FILE):
        conn = sqlite3.connect(DB_FILE)
        with open("init.sql") as f:
            conn.executescript(f.read())
        conn.close()

if __name__ == "__main__":
    init_db()
    app.run(host="0.0.0.0", port=5000)