FROM php:7.4-apache

# Enable PHP short tags and allow_url_include for filter wrappers
RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_include = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini

# Create document root
RUN mkdir -p /var/www/html/lfi-lab

# Copy application files
COPY app/ /var/www/html/lfi-lab/

# Copy flags directory outside web root
COPY flags/ /var/secrets/

# Create necessary directories
RUN mkdir -p /var/www/html/lfi-lab/level1 /var/www/html/lfi-lab/level2 /var/www/html/lfi-lab/level3 /var/www/html/lfi-lab/level4 /var/www/html/lfi-lab/level5 /var/www/html/lfi-lab/uploads /var/www/html/lfi-lab/logs

# Set permissions
RUN chown -R www-data:www-data /var/www/html/lfi-lab && \
    chmod -R 755 /var/www/html/lfi-lab && \
    chmod 644 /var/secrets/*.txt

# Create some dummy files for practice
RUN echo "This is a test file for level 1 practice." > /var/www/html/lfi-lab/level1/test.txt && \
    echo "Level 2 test content." > /var/www/html/lfi-lab/level2/data.txt && \
    echo "Advanced level test." > /var/www/html/lfi-lab/level3/advanced.txt && \
    echo "Filter wrapper test." > /var/www/html/lfi-lab/level4/filter.txt && \
    echo "Log poisoning test." > /var/www/html/lfi-lab/level5/poison.txt

EXPOSE 80

RUN echo '<?php header("Location: /lfi-lab/"); ?>' > /var/www/html/index.php && \
    chown www-data:www-data /var/www/html/index.php

CMD ["apache2-foreground"]
