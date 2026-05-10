FROM php:8.1-apache

# Install SQLite3 extension
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY app/ /var/www/html/

# Create database directory and set permissions
RUN mkdir -p /var/www/html/database && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Copy flag generator tool to container (for reference)
COPY tools/ /tmp/tools/

# Expose port (will be mapped randomly)
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
