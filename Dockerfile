FROM registry.fzhxkj.com/uyiban-service-env

COPY ./SocketServer.php /var/www/html/

EXPOSE 9505

CMD ["php", "/var/www/html/SocketServer.php"]


