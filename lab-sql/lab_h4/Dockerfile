FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY src/ /var/www/html/
COPY generate_flag.py /app/generate_flag.py
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
