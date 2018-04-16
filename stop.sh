#!/bin/bash
#
# Video Encode System Stop Script
# Version 0.2-alpha
#
echo -e "\033[0;34m+Stop Nginx\033[0m"
/usr/local/nginx/sbin/nginx -s stop
echo -e "\033[0;34m+Stop PHP-FPM\033[0m"
service php-fpm stop
echo -e "\033[0;34m+Stop Redis\033[0m"
service redis stop
echo -e "\033[0;34m+Stop Mysql\033[0m"
service mysqld stop
echo -e "\033[0;34m+Stop Main Thread\033[0m"
screen -S main_thread -X quit
echo -e "\033[0;34m+Unlock System\033[0m"
rm -rf start.lock
