FROM php:7.4-apache

# Enable mod_rewrite for URL routing
RUN a2enmod rewrite

# Install curl extension for PHP
RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install curl 2>/dev/null || echo "curl may be pre-installed"

# Create document root
RUN mkdir -p /var/www/html/ssrf-lab

# Copy application files
COPY app/ /var/www/html/ssrf-lab/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/ssrf-lab && \
    chmod -R 755 /var/www/html/ssrf-lab

# Configure Apache with FallbackResource for URL routing
RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/ssrf-lab\n\
    <Directory /var/www/html/ssrf-lab>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/ssrf-lab.conf && \
    a2dissite 000-default.conf && \
    a2ensite ssrf-lab.conf

EXPOSE 80

CMD ["apache2-foreground"]
