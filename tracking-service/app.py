from flask import Flask, request, jsonify
import sqlite3
import os
import time

app = Flask(__name__)

DB_PATH = os.environ.get('DB_PATH', '/data/tracking.db')


def get_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn


def init_db():
    conn = get_db()
    conn.execute('''CREATE TABLE IF NOT EXISTS hits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lab TEXT NOT NULL,
        ip TEXT DEFAULT '',
        timestamp REAL NOT NULL
    )''')
    conn.execute('''CREATE TABLE IF NOT EXISTS flag_captures (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lab TEXT NOT NULL,
        flag TEXT NOT NULL,
        ip TEXT DEFAULT '',
        timestamp REAL NOT NULL
    )''')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_hits_lab ON hits(lab)')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_flags_lab ON flag_captures(lab)')
    conn.commit()
    conn.close()


init_db()


@app.route('/api/hit', methods=['GET', 'POST'])
def record_hit():
    lab = request.values.get('lab', '')
    ip = request.values.get('ip', '')
    if not lab:
        return jsonify({'error': 'lab parameter required'}), 400

    conn = get_db()
    conn.execute('INSERT INTO hits (lab, ip, timestamp) VALUES (?, ?, ?)',
                 (lab, ip, time.time()))
    conn.commit()
    count = conn.execute('SELECT COUNT(*) as c FROM hits WHERE lab = ?', (lab,)).fetchone()['c']
    conn.close()
    return jsonify({'status': 'ok', 'lab': lab, 'hits': count})


@app.route('/api/flag', methods=['GET', 'POST'])
def record_flag():
    lab = request.values.get('lab', '')
    flag = request.values.get('flag', '')
    ip = request.values.get('ip', '')
    if not lab or not flag:
        return jsonify({'error': 'lab and flag parameters required'}), 400

    conn = get_db()
    conn.execute('INSERT INTO flag_captures (lab, flag, ip, timestamp) VALUES (?, ?, ?, ?)',
                 (lab, flag, ip, time.time()))
    conn.commit()
    count = conn.execute('SELECT COUNT(*) as c FROM flag_captures WHERE lab = ?', (lab,)).fetchone()['c']
    conn.close()
    return jsonify({'status': 'ok', 'lab': lab, 'captures': count})


@app.route('/api/stats', methods=['GET'])
def get_stats():
    conn = get_db()

    hits = conn.execute(
        'SELECT lab, COUNT(*) as count FROM hits GROUP BY lab'
    ).fetchall()

    flags = conn.execute(
        'SELECT lab, COUNT(*) as count FROM flag_captures GROUP BY lab'
    ).fetchall()

    total_hits = conn.execute('SELECT COUNT(*) as c FROM hits').fetchone()['c']
    total_flags = conn.execute('SELECT COUNT(*) as c FROM flag_captures').fetchone()['c']

    recent_flags = conn.execute(
        'SELECT lab, flag, ip, timestamp FROM flag_captures ORDER BY timestamp DESC LIMIT 50'
    ).fetchall()

    conn.close()

    return jsonify({
        'total_hits': total_hits,
        'total_flag_captures': total_flags,
        'hits_by_lab': {row['lab']: row['count'] for row in hits},
        'flags_by_lab': {row['lab']: row['count'] for row in flags},
        'recent_flags': [{'lab': r['lab'], 'flag': r['flag'], 'ip': r['ip'],
                          'time': r['timestamp']} for r in recent_flags]
    })


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok'})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8080)
