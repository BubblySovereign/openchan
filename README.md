openchan
========================================================

About
------------
openchan is a fork of vichan, with the difference that openchan is geared towards being lightweight.

Installation
------------
Basic requirements:
A computer running a Unix or Unix-like OS(openchan has been specifically tested with and is known to work under Ubuntu 26.x), nginx, MySQL, and PHP
* Make sure nginx has read/write access to the directory infinity resides in.
* `install.php` is not maintained. Don't use it.

Step 1. Create openchan's database from the included install.sql file. Enter mysql and create an empty database named 'openchan'.
```
mysql -uroot -p openchan < install.sql
```

Step 2. Openchan can function in a *very* barebones fashion after the first two steps, but you should probably install these additional packages if you want to seriously run it and/or contribute to it. Make sure to run the below as root:

```
apt install php-{fpm,bcmath,gd,pdo,mbstring,mysql,redis} composer mariadb-server graphicsmagick gifsicle git nginx vim python3-certbot-nginx
```

Please remember to change the administrator account password.

License
--------
See [LICENSE](https://github.com/BubblySovereign/openchan/blob/master/LICENSE).
