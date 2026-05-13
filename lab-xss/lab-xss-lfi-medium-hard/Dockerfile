FROM php:8.2-apache

# Install Python for flag generation
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY app/ /var/www/html/

# Make flag generator executable (mounted at runtime)
RUN chmod +x /usr/local/bin/generate_flag.py 2>/dev/null || true

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
