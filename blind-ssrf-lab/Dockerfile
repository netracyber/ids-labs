FROM php:7.4-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install curl 2>/dev/null || echo "curl may be pre-installed"

RUN mkdir -p /var/www/html/blind-ssrf-lab

COPY app/ /var/www/html/blind-ssrf-lab/
COPY flags/ /var/www/html/blind-ssrf-lab/flags/

RUN chown -R www-data:www-data /var/www/html/blind-ssrf-lab && \
    chmod -R 755 /var/www/html/blind-ssrf-lab

RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/blind-ssrf-lab\n\
    <Directory /var/www/html/blind-ssrf-lab>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/blind-ssrf.conf && \
    a2dissite 000-default.conf && \
    a2ensite blind-ssrf.conf

EXPOSE 80
CMD ["apache2-foreground"]
