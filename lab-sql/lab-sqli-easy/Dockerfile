FROM python:3.11-slim

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements and install Python dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application files
COPY app/ ./app/

# Create non-root user
RUN useradd -m -u 1000 labuser && \
    chown -R labuser:labuser /app

USER labuser

# Expose port (will be mapped randomly)
EXPOSE 8080

# Start the application
CMD ["python", "app/app.py"]
