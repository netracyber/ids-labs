FROM php:7.4-apache
RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_include = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini
COPY app/ /var/www/html/
COPY flags/ /var/secrets/
RUN chown -R www-data:www-data /var/www/html && chmod 644 /var/secrets/*.txt
RUN mkdir -p /var/log/apache2 && chmod -R 777 /var/log/apache2
RUN mkdir -p /var/www/html/downloads && \
    echo "Welcome to our download portal. This is a sample file." > /var/www/html/downloads/readme.txt && \
    echo "Product documentation v1.0" > /var/www/html/downloads/product.txt
EXPOSE 80
CMD ["apache2-foreground"]