FROM php:7.4-apache
RUN a2enmod rewrite headers
RUN echo "allow_url_include = On" >> /usr/local/etc/php/php.ini &&     echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini
COPY app/ /var/www/html/
COPY flags/ /var/flags/
RUN mkdir -p /var/log/apache2 &&     chmod -R 777 /var/log/apache2
RUN chown -R www-data:www-data /var/www/html &&     chmod -R 755 /var/www/html &&     chmod 644 /var/flags/*.txt
EXPOSE 80
CMD ["apache2-foreground"]
