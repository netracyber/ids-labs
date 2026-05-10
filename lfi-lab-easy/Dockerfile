FROM php:7.4-apache

# Enable PHP short tags
RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini

# Create document root
RUN mkdir -p /var/www/html/lfi-easy

# Copy application files
COPY app/ /var/www/html/lfi-easy/

# Copy flags directory outside web root
COPY flags/ /var/secrets/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/lfi-easy && \
    chmod -R 755 /var/www/html/lfi-easy && \
    chmod 644 /var/secrets/*.txt

EXPOSE 80

RUN echo '<?php header("Location: /lfi-easy/"); ?>' > /var/www/html/index.php && \
    chown www-data:www-data /var/www/html/index.php

CMD ["apache2-foreground"]
