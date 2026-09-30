Installation
-------------
1. Install packages
    ```apt install php-{fpm,bcmath,gd,pdo,mbstring,mysql,redis} composer mariadb-server imagemagick graphicsmagick gifsicle git nginx vim python3-certbot-nginx```
   
1.	Get the latest development version with:

        git clone https://github.com/BubblySovereign/vichan-fixed.git

2.	run ```composer install``` inside the directory
3.	Navigate to ```install.php``` in your web browser and follow the
	prompts.
4.	vichan should now be installed. Log in to ```mod.php``` with the
	default username and password combination: **admin / password**.

Please remember to change the administrator account password.

See also: [Configuration Basics](https://github.com/vichan-devel/vichan/wiki/config).

License
--------
See [LICENSE.md](http://github.com/vichan-devel/vichan/blob/master/LICENSE.md).
