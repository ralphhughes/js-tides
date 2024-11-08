# Dockerfile using Debian Bullseye based image
#FROM nginx:1.24-bullseye

FROM ubuntu:18.04

# Install PHP and xtide
RUN echo "deb http://archive.ubuntu.com/ubuntu bionic main restricted multiverse universe" > /etc/apt/sources.list && \
    apt-get update && \
    apt-get install -y nginx php-fpm xtide xtide-data xtide-data-nonfree && \
    apt-get clean


# Copy your NGINX configuration file (or you can configure it in the Dockerfile)
COPY ./default.conf /etc/nginx/sites-available/default

# Copy project files into image
COPY ./public /var/www/html

# Nginx is on port 80 internally
EXPOSE 80


# Copy a startup script to the container
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Start NGINX and PHP-FPM via the startup script
CMD ["/start.sh"]
