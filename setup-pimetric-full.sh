#!/bin/bash

########################################################################
#
# Installation script for Raspberry Pi
# 
# PiMetric
#
# 2018-07-20
#
########################################################################

# If editing with Geany or Windows based editors ensure that line
# endings are set to Unix (under Document/Line Endings in Geany)

# Assumptions:
#
# ssh access is already enabled
# (create a ssh file in the root dir of your sd card before turning on)
# The root partition has been resized and access is 
# available via eth0 or WiFi to a dynamic IP
# Once the scripts are transferred to the Pi (using WinSCP)
# make the setup script (this one) executable by right clicking on
# it WinSCP or by typing chmod +x ~/<scriptname>
# Directories that should be transferred are;
# core_scripts
# PiMetric
# Add your unique details in the set up parameters area below.


# exit if not running using sudo
if [ "$EUID" -ne 0 ]
	then echo "Must be root. Please use sudo."
	exit
fi

# Set up parameters. Enter your particular details here.
LOGFILE=/home/pi/install.log
HOSTNAME="pimetric-test"         # hostname of the Pi via pimetric.local

# Script steps

echo "PiMetric Installation"
echo "Starting update... (this may take a few minutes)"

# Update/upgrade the base OS
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : Update started" >> $LOGFILE 2>&1
apt-get update &>> $LOGFILE 2>&1

echo "Starting upgrade... This may take 10 minutes or so)"
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : Upgrade started" >> $LOGFILE 2>&1
apt-get upgrade -y &>> $LOGFILE 2>&1

echo "Downloading PiMetric..."
# Get PiMetric and set permissions
#
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Downloading PiMetric and setting permissions" >> $LOGFILE 2>&1
echo >> $LOGFILE 2>&1

cd /srv
curl -L https://github.com/d3noob/PiMetric/archive/master.zip > pimetric.zip

echo "Decompressing the files..."

unzip pimetric.zip
mv PiMetric-master PiMetric

echo "Setting the permissions..."

chown -R pi:www-data /srv/PiMetric/
chmod -R 775 /srv/PiMetric/
usermod -a -G www-data pi
chmod 666 /srv/PiMetric/monitoring/monitoring

# Web Server
# A web server with Nginx and PHP installed

echo "Starting NGINX install..."
# install nginx
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Ngnix install started" >> $LOGFILE 2>&1
apt-get install nginx -y &>> $LOGFILE 2>&1

echo "Starting PHP install..."
# install php-fpm
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
PHP install started" >> $LOGFILE 2>&1
apt-get install php-fpm -y &>> $LOGFILE 2>&1

echo "Setting up the sites file..."
# Set up the sites file
sed -i -- 's/index index.html index.htm index.nginx-debian.html;/index index.html index.htm index.php;/g' /etc/nginx/sites-available/default
sed -i -- 's/root \/var\/www\/html;/root \/srv\/PiMetric\/html;/g' /etc/nginx/sites-available/default
sed -i "/pass PHP scripts to FastCGI server/ r /dev/stdin" /etc/nginx/sites-available/default <<'EOF'
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		# With php-fpm (or other unix sockets):
		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
	}
EOF

echo "Creating an index.php file..."
# Create an `index.php` file
echo "<?php phpinfo(); ?>" > /srv/PiMetric/html/index.php

echo "Restarting Nginx..."
# Restart Nginx
/etc/init.d/nginx restart


# Checking dncpcd.conf
SITESCONFIG='
	location ~ \\.php$ {
		include snippets/fastcgi-php.conf;
		# With php-fpm (or other unix sockets):
		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
	}'

SITESFILE=`cat /etc/nginx/sites-available/default`

if [[ $SITESFILE != *$SITESCONFIG* ]]; then
    echo "$(date "+%Y-%m-%d %T") : \
    ***Error*** /etc/nginx/sites-available/default not correct" >> $LOGFILE 2>&1
    exit
fi

# Checking for presence of index.php
if [ ! -f /srv/PiMetric/html/index.php ]; then
    echo "/srv/PiMetric/html/index.php missing."
    echo "$(date "+%Y-%m-%d %T") : \
    ***Error*** /srv/PiMetric/html/index.php missing" >> $LOGFILE 2>&1
    exit
fi

# Check to see if Nginx is running
if [ ! -e /var/run/nginx.pid ]; then 
    echo "Nginx not running."
    echo "$(date "+%Y-%m-%d %T") : \
    ***Error*** Nginx not running" >> $LOGFILE 2>&1
    exit
fi

echo "Installing SQLite..."
# install SQLite
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
SQLite install started" >> $LOGFILE 2>&1
apt-get install sqlite3 php7.0-sqlite3 -y &>> $LOGFILE 2>&1

echo "Installing python3-pip..."
# install python3-pip
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
python3-pip install started" >> $LOGFILE 2>&1
apt-get install python3-pip -y &>> $LOGFILE 2>&1

echo "Installing snmp snmpd snmp-mibs-downloader libsnmp-dev..."
# install snmp snmpd snmp-mibs-downloader libsnmp-dev
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
snmp snmpd snmp-mibs-downloader libsnmp-dev install started" >> $LOGFILE 2>&1
apt-get install snmp snmpd snmp-mibs-downloader libsnmp-dev -y &>> $LOGFILE 2>&1

echo "Installing speedtest-cli..."
# install speedtest-cli
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
speedtest-cli install started" >> $LOGFILE 2>&1
apt-get install speedtest-cli -y &>> $LOGFILE 2>&1

echo "Installing pip3 module requests..."
# install Python Module requests via pip3
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module requests install started" >> $LOGFILE 2>&1
pip3 install requests &>> $LOGFILE 2>&1

echo "Installing pip3 module mysqlclient..."
# install Python Module mysqlclient via pip3
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module mysqlclient install started" >> $LOGFILE 2>&1
pip3 install mysqlclient &>> $LOGFILE 2>&1

echo "Installing pip3 module lxml..."
# install Python Module lxml via pip3
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module lxml install started" >> $LOGFILE 2>&1
pip3 install lxml &>> $LOGFILE 2>&1

echo "Installing pip3 module cssselect..."
# install Python Module cssselect via pip3
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module cssselect install started" >> $LOGFILE 2>&1
pip3 install cssselect &>> $LOGFILE 2>&1

echo "Installing pip3 module python3-netsnmp..."
# install Python Module python3-netsnmp via pip3
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module python3-netsnmp install started" >> $LOGFILE 2>&1
pip3 install python3-netsnmp &>> $LOGFILE 2>&1

echo "Setting Hostname..."
# Therefore to set the hostname
raspi-config nonint do_hostname $HOSTNAME

# At the end of the installation a reboot will be required.

echo "add the crontab lines"

echo "############################"
echo "All finished. Please reboot."
echo "############################"

# Reboot
# sudo reboot
