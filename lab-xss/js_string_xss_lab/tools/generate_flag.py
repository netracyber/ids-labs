import secrets
import string
import os

def generate_random_flag():
    """Generate a random flag in the format IDS{code_acak...}"""
    random_code = secrets.token_hex(16)
    flag = f"IDS{{{random_code}}}"
    return flag

def save_flag_to_file(flag, filename="current_flag.txt"):
    """Save the generated flag to a file"""
    with open(filename, 'w') as f:
        f.write(flag)
    print(f"Flag saved to {filename}: {flag}")
    return flag

if __name__ == "__main__":
    flag = generate_random_flag()
    print(f"Generated flag: {flag}")
    save_flag_to_file(flag)
