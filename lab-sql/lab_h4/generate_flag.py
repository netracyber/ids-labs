import secrets
import os

def generate_hex_flag():
    """Generate a random flag with 32 hex characters"""
    random_code = secrets.token_hex(16)  # 16 bytes = 32 hex chars
    return f'IDS{{{random_code}}}'

if __name__ == "__main__":
    flag = generate_hex_flag()
    # Save to file
    flag_path = '/tmp/flag.txt'
    with open(flag_path, 'w') as f:
        f.write(flag)
    print(flag)
