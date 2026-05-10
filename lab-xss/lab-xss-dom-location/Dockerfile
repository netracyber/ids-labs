FROM php:8.2-apache

# Install Python for flag generation
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite and mod_headers
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /usr/local/apache2/htdocs/

# Copy application files
COPY app/ /usr/local/apache2/htdocs/

# Make flag generator accessible
RUN chmod +x /usr/local/bin/generate_flag.py 2>/dev/null || true

# Set proper permissions
RUN chown -R www-data:www-data /usr/local/apache2/htdocs/

# Expose port 80 (will be mapped randomly)
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
