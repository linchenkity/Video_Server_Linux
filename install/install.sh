#!/bin/bash
echo -e "\033[1;33m[Video Encode System For Linux] \033[0m"
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
yum -y install php70w php70w-mysql php70w-mbstring php70w-mcrypt php70w-gd php70w-imap php70w-ldap php70w-odbc php70w-pear php70w-xml php70w-xmlrpc php70w-pdo php70w-fpm