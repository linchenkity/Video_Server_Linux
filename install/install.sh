#!/bin/bash
#
# Video Encode System Install Script
# Version 0.2-alpha
#
echo -e "\033[1;33m[Video Encode System For Linux] \033[0m"
echo -e "\033[1;32m[Version: 0.2-Alpha] \033[0m"
echo -e "\033[1;37m[Build By:haha_Dashen]"
echo -e "[Follow MIT License]\033[0m"
echo -e "\033[0;34m+Change YUM Base to 163........\033[0m"
sleep 2
time_start=$(date +%s)
mv /etc/yum.repos.d/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo.backup
wget -O /etc/yum.repos.d/CentOS-Base.repo http://mirrors.163.com/.help/CentOS6-Base-163.repo
yum clean all
yum makecache
echo -e "\033[0;34m+Initialization System........\033[0m"
yum -y install wget
yum -y install epel-release
yum -y install gcc automake autoconf libtool gcc-c++
yum -y install gd zlib zlib-devel openssl openssl-devel libxml2 libxml2-devel libjpeg libjpeg-devel libpng libpng-devel freetype freetype-devel libmcrypt libmcrypt-devel
yum -y install cur curl-devel libxslt libxslt-devel
echo -e "\033[0;34m+Install Mysql\033[0m"
sleep 2
rpm -Uvh http://repo.mysql.com/mysql-community-release-el6-5.noarch.rpm
yum -y install mysql-community-server
echo -e "\033[0;34m+Install PCRE\033[0m"
sleep 2
cd /usr/local/src
wget https://ftp.pcre.org/pub/pcre/pcre2-10.31.tar.gz
tar xzvf pcre2-10.31.tar.gz
cd pcre2-10.31
./configure --prefix=/usr/local/src/pcre2-10.31
make
make install
yum -y install pcre-devel
mkdir /usr/local/src/pcre2-10.31/.libs
cp /usr/lib64/libpcre.so /usr/local/src/pcre2-10.31/libpcre.a
cp /usr/lib64/libpcre.so /usr/local/src/pcre2-10.31/libpcre.la
cp /usr/lib64/libpcre.so /usr/local/src/pcre2-10.31/.libs/libpcre.a
cp /usr/lib64/libpcre.so /usr/local/src/pcre2-10.31/.libs/libpcre.la
echo -e "\033[0;34m+Install OpenSSL\033[0m"
sleep 2
cd /usr/local/src
wget https://www.openssl.org/source/openssl-1.1.0h.tar.gz
tar xzvf openssl-1.1.0h.tar.gz
cd openssl-1.1.0h
./config
./config -t
make
make install
echo -e "\033[0;34m+Install Nginx\033[0m"
sleep 2
cd /usr/local/src
wget http://nginx.org/download/nginx-1.12.2.tar.gz
tar xzvf nginx-1.12.2.tar.gz
cd nginx-1.12.2
./configure
make
make install
rm -rf /usr/local/nginx/conf/nginx.conf
cp /home/Video_Server_Linux/install/nginx.conf /usr/local/nginx/conf/nginx.conf
cp /home/Video_Server_Linux/install/Web_GUI.conf /usr/local/nginx/conf/Web_GUI.conf
cp /home/Video_Server_Linux/install/Video_Service.conf /usr/local/nginx/conf/Video_Service.conf
cp /home/Video_Server_Linux/install/OpenAPI.conf /usr/local/nginx/conf/OpenAPI.conf
echo -e "\033[0;34m+Install PHP\033[0m"
sleep 2
cd /usr/local/src
rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm
yum -y install php70w php70w-mysql php70w-mbstring php70w-mcrypt php70w-gd php70w-imap php70w-ldap php70w-odbc php70w-pear php70w-xml php70w-xmlrpc php70w-pdo php70w-fpm php70w-devel
echo -e "\033[0;34m+Install Redis\033[0m"
sleep 2
yum -y install redis
echo -e "\033[0;34m+Install PHP-Redis\033[0m"
sleep 2
cd /usr/local/src
wget http://pecl.php.net/get/redis-4.0.0.tgz
tar xzvf redis-4.0.0.tgz
cd redis-4.0.0
phpize
./configure
make
make install
cp -rf /home/Video_Server_Linux/install/php.ini /etc/php.ini
echo -e "\033[0;34m+Config Mysql\033[0m"
sleep 2
service mysqld start
mysql_password=$(head -200 /dev/urandom | cksum | cut -f1 -d" ")
/usr/bin/mysqladmin -u root password $mysql_password
service mysqld restart
echo -e "\033[0;34m+Start Nginx\033[0m"
/usr/local/nginx/sbin/nginx
echo -e "\033[0;34m+Start PHP-FPM\033[0m"
service php-fpm start
echo -e "\033[0;34m+Start Redis\033[0m"
service redis start
echo -e "\033[0;34m+Disable Iptables\033[0m"
service iptables stop
echo -e "\033[0;34m+Import Database\033[0m"
cd /home/Video_Server_Linux/install
mysql -uroot -p$mysql_password -e "CREATE DATABASE video_server"
mysql -uroot -p$mysql_password video_server < database_v0.2-alpha.sql
echo -e "\033[1;32m"
echo -e "#####################################"
echo -e "#                                   #"
echo -e "#        Video Encode System        #"
echo -e "#                                   #"
echo -e "#####################################"
echo -e "\033[0m"
time_end=$(date +%s)
total_time=$(($time_end - time_start))
echo -e "\033[1;33mMysql Password:$mysql_password\033[0m";
echo -e "\033[1;33mDone! $total_time Seconds Used\033[0m";