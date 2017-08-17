FROM registry.fzhxkj.com/uyiban-service-env

COPY . /var/www/html/

WORKDIR /var/www/html
EXPOSE 9505

CMD ["php", "/var/www/html/SocketServer.php"]


