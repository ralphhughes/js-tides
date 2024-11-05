# Dockerfile
FROM nginx:latest

RUN apt-get update && apt-get install php-fpm xtide && apt-get clean

RUN echo "server { \
    listen 80; \
    root /usr/share/nginx/html; \
    index index.php index.html index.htm; \
    location / { \
         try_files \$uri \$uri/ =404; \
    } \
    location ~ \.php$ { \
         include snippets/fastcgi-php.conf; \
         fastcgi_pass unix:/var/run/php/php-fpm.sock; \
    } \
}" > /etc/nginx/conf.d/default.conf

COPY ./ /usr/share/nginx/html

EXPOSE 80

CMD service php7.4-fpm start && nginx -g 'daemon off;'
