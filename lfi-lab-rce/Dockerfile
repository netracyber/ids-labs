FROM php:7.4-apache
RUN echo "short_open_tag = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_include = On" >> /usr/local/etc/php/php.ini && \
    echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini
ENV SECRET_FLAG=IDS{b53aaf8b92d6d11c66a4d0a1283fc1d8}
COPY app/ /var/www/html/
COPY flags/ /var/secrets/
RUN chown -R www-data:www-data /var/www/html && chmod 644 /var/secrets/*.txt
RUN mkdir -p /var/log/apache2 && chmod -R 777 /var/log/apache2
RUN mkdir -p /tmp/sessions && chmod -R 777 /tmp/sessions
RUN echo "session.save_path = /tmp/sessions" >> /usr/local/etc/php/php.ini
# Create environ dump directory for Level 1
RUN mkdir -p /var/www/environ
COPY start.sh /start.sh
RUN chmod +x /start.sh
EXPOSE 80
CMD ["/start.sh"]