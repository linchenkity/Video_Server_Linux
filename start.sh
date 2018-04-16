#!/bin/bash
#
# Video Encode System Start Script
# Version 0.2-alpha
#
if [ -f "start.lock" ];then
echo -e "\033[1;31mSystem Locked (Lock File:start.lock)\033[0m"
exit
else
echo -e "\033[0;34m+Start Nginx\033[0m"
/usr/local/nginx/sbin/nginx
echo -e "\033[0;34m+Start PHP-FPM\033[0m"
php-fpm -R
echo -e "\033[0;34m+Start Redis\033[0m"
service redis start
echo -e "\033[0;34m+Start Mysql\033[0m"
service mysqld start
echo -e "\033[0;34m+Start Main Thread\033[0m"
screen -dmS main_thread
screen -x -S main_thread -p 0 -X stuff "php main.php"
screen -x -S main_thread -p 0 -X stuff $'\n'
touch start.lock
fi