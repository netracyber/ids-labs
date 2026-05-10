FROM php:7.4-apache

# Enable mod_rewrite for URL routing
RUN a2enmod rewrite

# Copy application files
COPY src/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 644 /var/www/html/flags/*.txt

# Configure Apache with FallbackResource for URL routing
RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/internal-api.conf && \
    a2dissite 000-default.conf && \
    a2ensite internal-api.conf

EXPOSE 80

CMD ["apache2-foreground"]
