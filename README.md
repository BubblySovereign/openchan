openchan
========================================================

About
------------
openchan is a fork of vichan, with the difference that openchan is geared towards being lightweight.

Installation
-------------
1. Install packages
    ```apt install php-{fpm,bcmath,gd,pdo,mbstring,mysql,redis} composer mariadb-server graphicsmagick gifsicle git nginx vim python3-certbot-nginx```
   
1.	Get the latest development version with:

        git clone https://github.com/BubblySovereign/openchan.git

2.	run ```composer install``` inside the directory
3.	Navigate to ```install.php``` in your web browser and follow the
	prompts.
4.	openchan should now be installed. Log in to ```mod.php``` with the
	default username and password combination: **admin / password**.

Please remember to change the administrator account password.

License
--------
See [LICENSE](https://github.com/BubblySovereign/openchan/blob/master/LICENSE).
