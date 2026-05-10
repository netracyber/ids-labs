FROM php:7.4-apache
RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_include = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini
# Enable .htaccess overrides for Level 4
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
COPY app/ /var/www/html/
COPY flags/ /var/secrets/
RUN chown -R www-data:www-data /var/www/html && chmod 644 /var/secrets/*.txt
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads
RUN mkdir -p /var/log/apache2 && chmod -R 777 /var/log/apache2
EXPOSE 80
CMD ["apache2-foreground"]
