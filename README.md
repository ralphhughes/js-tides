# js-tides

Simple web-based tidal charting app. Basically a Javascript frontend (using chart.js) for the linux xtide program.

## Install process

On your linux web server, enable PHP, install xtide, copy this code to your web root, visit it in a web browser

Example for Ubuntu Linux 18.04:
```
$ sudo apt install xtide

$ sudo apt install nginx php-fpm

$ echo "server {
	root /var/www/html;
	index index.php index.html index.htm;
	location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.2-fpm.sock;
        }

}
" >> /etc/nginx/sites-enabled/default

$ cd /var/www/html

$ git clone https://github.com/ralphhughes/js-tides.git .

```

