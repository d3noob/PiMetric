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

echo "##############################"
echo "Starting update..."
echo "##############################"

# Update/upgrade the base OS
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : Update started" >> $LOGFILE 2>&1
apt-get update &>> $LOGFILE 2>&1

echo "##############################"
echo "Starting upgrade..."
echo "##############################"

echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : Upgrade started" >> $LOGFILE 2>&1
apt-get upgrade -y &>> $LOGFILE 2>&1

echo "#########################"
echo "Downloading PiMetric..."
echo "#########################"

# Get PiMetric and set permissions
#
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Downloading PiMetric and setting permissions" >> $LOGFILE 2>&1
echo >> $LOGFILE 2>&1

cd /srv
curl -L https://github.com/d3noob/PiMetric/archive/master.zip > pimetric.zip
unzip pimetric.zip
mv PiMetric-master PiMetric

echo "Setting the permissions..."

chown -R pi:www-data /srv/PiMetric/
chmod -R 775 /srv/PiMetric/
usermod -a -G www-data pi
chmod 666 /srv/PiMetric/monitoring/monitoring


echo "#################################"
echo "Starting Nginx and PHP install..."
echo "#################################"

# Web Server
# A web server with Nginx and PHP installed

# install nginx and php-fpm
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Ngnix / PHP install started" >> $LOGFILE 2>&1
apt-get install nginx php-fpm -y &>> $LOGFILE 2>&1

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


echo "##########################"
echo "Starting SQLite install..."
echo "##########################"

# install SQLite and access programs
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
SQLite and access programs install started" >> $LOGFILE 2>&1
apt-get install sqlite3 python3-pip php7.0-sqlite3 snmp snmpd snmp-mibs-downloader libsnmp-dev speedtest-cli -y &>> $LOGFILE 2>&1

echo "###########################################"
echo "Starting Python Modules via pip3 install..."
echo "###########################################"

# install Python Modules via pip3
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Modules via pip3 install started" >> $LOGFILE 2>&1
pip3 install requests mysqlclient lxml cssselect python3-netsnmp &>> $LOGFILE 2>&1



echo "###################"
echo "Setting Hostname..."
echo "###################"

# Setting the hostname
# These are commands that use raspi-config in noninteractive mode
# https://github.com/raspberrypi-ui/rc_gui/blob/master/src/rc_gui.c#L23-L70
# raspi-config nonint functions obey sh return codes 

# Therefore to set the hostname
raspi-config nonint do_hostname $HOSTNAME

# At the end of the installation a reboot will be required.


echo "add the crontab lines"



echo "############################"
echo "All finished. Please reboot."
echo "############################"

# Reboot
# sudo reboot
