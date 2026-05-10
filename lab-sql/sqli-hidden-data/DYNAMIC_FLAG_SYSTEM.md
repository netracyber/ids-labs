# Dynamic Flag System

This SQL injection lab implements a dynamic flag system that generates a unique flag for each instance of the application.

## How it works:

1. When the application starts for the first time, it generates a random flag in the format `IDS{code_acak...}`
2. The flag is saved to `current_flag.txt` in the application directory
3. The same flag is used consistently throughout the lifetime of that instance
4. When hidden products are successfully retrieved via SQL injection, this flag is displayed

## Flag Format:

- Format: `IDS{xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx}` where x is a hexadecimal character
- Length: 32 characters in the code part (16 bytes represented as hex)

## For CTF Administrators:

- Each deployment of the lab will have a different flag
- The flag remains consistent during the lifetime of the container
- To get the flag for verification, check the `current_flag.txt` file in the container
- The flag is also displayed when the challenge is completed successfully