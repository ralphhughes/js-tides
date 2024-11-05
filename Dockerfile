# Dockerfile
FROM nginx:1.24-bullseye

RUN echo "deb http://deb.debian.org/debian bullseye main contrib non-free" > /etc/apt/sources.list && \
    apt-get update && \
    apt-get install -y php-fpm xtide xtide-data xtide-data-nonfree && \
    apt-get clean

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
