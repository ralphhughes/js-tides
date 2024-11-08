#!/bin/bash

# Start PHP-FPM
service php7.2-fpm start

# Start NGINX in the foreground
nginx -g 'daemon off;'

