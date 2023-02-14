# js-tides

Simple web-based tidal charting app

## Install process

On your linux web server, enable PHP, install xtide, copy this code to your web root, visit it in a web browser

Example for Ubuntu Linux 18.04:
```
$ sudo apt install xtide

$ sudo apt install nginx

echo "server {
	root /var/www/html;
	index index.php index.html index.htm;
	location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.2-fpm.sock;
        }

}
" >> /etc/nginx/sites-enabled/default

$ cd /var/www/html

$ git clone https://github.com/ralphhughes/js-tides.git


```

Llandudno Tidal heights
{
	"atlas": "BODC",
	"station": "Llandudno",
	"requestDatum": "CD",
	"responseDatum": "CD",
	"datums": [
		{
			"name": "HAT",
			"height": 8.675
		},
		{
			"name": "MHHWS",
			"height": 7.829
		},
		{
			"name": "MHWS",
			"height": 7.707
		},
		{
			"name": "MLHWS",
			"height": 7.584
		},
		{
			"name": "MHHW",
			"height": 6.997
		},
		{
			"name": "MHW",
			"height": 6.91
		},
		{
			"name": "MLHW",
			"height": 6.805
		},
		{
			"name": "MHHWN",
			"height": 6.033
		},
		{
			"name": "MHWN",
			"height": 5.938
		},
		{
			"name": "MLHWN",
			"height": 5.842
		},
		{
			"name": "MTL",
			"height": 4.091
		},
		{
			"name": "MHLWN",
			"height": 2.34
		},
		{
			"name": "MLWN",
			"height": 2.179
		},
		{
			"name": "MLLWN",
			"height": 2.018
		},
		{
			"name": "MHLW",
			"height": 1.368
		},
		{
			"name": "MLW",
			"height": 1.271
		},
		{
			"name": "MLLW",
			"height": 1.16
		},
		{
			"name": "MHLWS",
			"height": 0.651
		},
		{
			"name": "MLWS",
			"height": 0.516
		},
		{
			"name": "MLLWS",
			"height": 0.382
		},
		{
			"name": "LAT",
			"height": -0.507
		},
		{
			"name": "CD",
			"height": 0
		},
		{
			"name": "MSL",
			"height": 4.06
		}
	]
}