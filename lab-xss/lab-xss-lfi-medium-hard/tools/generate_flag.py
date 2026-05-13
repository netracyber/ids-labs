import secrets

def generate_random_flag():
    random_code = secrets.token_hex(16)
    return f"IDS{{{random_code}}}"

if __name__ == "__main__":
    flag = generate_random_flag()
    print(f"Generated flag: {flag}")
    with open("current_flag.txt", "w") as f:
        f.write(flag)
