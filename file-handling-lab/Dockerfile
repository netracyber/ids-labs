FROM php:7.4-apache

# Enable PHP settings for LFI/RFI lab
RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_include = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini

# Copy application files
COPY app/ /var/www/html/

# Copy flags directory outside web root
COPY flags/ /var/flags/

# Create pages directory for Level 2 (base path prefix)
RUN mkdir -p /var/www/html/pages && \
    echo "<h1>Welcome to Page Viewer</h1><p>Select a page to view.</p>" > /var/www/html/pages/home.txt && \
    echo "<h1>About Us</h1><p>This is a simple page viewer application.</p>" > /var/www/html/pages/about.txt

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 644 /var/flags/*.txt

# Ensure log directory exists and is writable for Level 5
RUN mkdir -p /var/log/apache2 && \
    chmod -R 777 /var/log/apache2

EXPOSE 80

CMD ["apache2-foreground"]
