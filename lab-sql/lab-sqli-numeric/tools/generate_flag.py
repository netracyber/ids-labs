import secrets
import string
import os

def generate_random_flag():
    """Generate a random flag in the format IDS{code_acak...}"""
    # Generate a random string of 32 characters (hexadecimal)
    random_code = secrets.token_hex(16)
    flag = f"IDS{{{random_code}}}"
    return flag

def generate_random_flag_alphanumeric():
    """Generate a random flag with alphanumeric characters in the format IDS{code_acak...}"""
    # Generate a random string of 16-32 alphanumeric characters
    length = 20  # Fixed length for consistency
    characters = string.ascii_letters + string.digits
    random_code = ''.join(secrets.choice(characters) for _ in range(length))
    flag = f"IDS{{{random_code}}}"
    return flag

def save_flag_to_file(flag, filename="current_flag.txt"):
    """Save the generated flag to a file"""
    with open(filename, 'w') as f:
        f.write(flag)
    print(f"Flag saved to {filename}: {flag}")
    return flag

def get_existing_flag_or_generate(filename="current_flag.txt"):
    """Get existing flag from file or generate a new one if file doesn't exist"""
    if os.path.exists(filename):
        with open(filename, 'r') as f:
            flag = f.read().strip()
        print(f"Existing flag found: {flag}")
        return flag
    else:
        flag = generate_random_flag()
        save_flag_to_file(flag, filename)
        return flag

if __name__ == "__main__":
    print("Flag Generator for SQL Injection Lab")
    print("=====================================")

    # Generate a new random flag
    new_flag = generate_random_flag()
    print(f"Generated flag: {new_flag}")

    # Save to file
    save_flag_to_file(new_flag)

    # Also demonstrate getting existing flag or generating new one
    print("\nUsing get_existing_flag_or_generate function:")
    flag = get_existing_flag_or_generate()
    print(f"Result flag: {flag}")
