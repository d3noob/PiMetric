#!/bin/bash

########################################################################
#
# Installation script for Raspberry Pi
# 
# PiMetric
#
# 2018-07-21
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
# Once the setup-pimetric-full.sh script is transferred to the Pi
# make it executable by right clicking on
# it WinSCP or by typing chmod +x ~/setup-pimetric-full.sh
# 
# Add your unique details in the set up parameters area below.
# This will be the HOSTNAME and the location of the log file


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
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Downloading PiMetric and setting permissions" >> $LOGFILE 2>&1
echo >> $LOGFILE 2>&1

# Download the PiMetric files
cd /srv
curl -L https://github.com/d3noob/PiMetric/archive/master.zip > pimetric.zip

# Unzip the files
echo "Decompressing the files..."
unzip pimetric.zip
mv PiMetric-master PiMetric

# Change the permissions
echo "Setting the permissions..."
chown -R pi:www-data /srv/PiMetric/
chmod -R 775 /srv/PiMetric/
usermod -a -G www-data pi
chmod 666 /srv/PiMetric/monitoring/monitoring

# Web Server
# A web server with Nginx and PHP installed

# install nginx
echo "Starting NGINX install..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Ngnix install started" >> $LOGFILE 2>&1
apt-get install nginx -y &>> $LOGFILE 2>&1

# install php-fpm
echo "Starting PHP install..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
PHP install started" >> $LOGFILE 2>&1
apt-get install php-fpm -y &>> $LOGFILE 2>&1

# Set up the sites file
echo "Setting up the sites file..."
sed -i -- 's/index index.html index.htm index.nginx-debian.html;/index index.html index.htm index.php;/g' /etc/nginx/sites-available/default
sed -i -- 's/root \/var\/www\/html;/root \/srv\/PiMetric\/html;/g' /etc/nginx/sites-available/default
sed -i "/pass PHP scripts to FastCGI server/ r /dev/stdin" /etc/nginx/sites-available/default <<'EOF'
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		# With php-fpm (or other unix sockets):
		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
	}
EOF

# Create an `index.php` file
echo "Creating an index.php file..."
echo "<?php phpinfo(); ?>" > /srv/PiMetric/html/index.php

# Restart Nginx
echo "Restarting Nginx..."
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

# install SQLite
echo "Installing SQLite..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
SQLite install started" >> $LOGFILE 2>&1
apt-get install sqlite3 php7.0-sqlite3 -y &>> $LOGFILE 2>&1

# install python3-pip
echo "Installing python3-pip..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
python3-pip install started" >> $LOGFILE 2>&1
apt-get install python3-pip -y &>> $LOGFILE 2>&1

# install snmp snmpd snmp-mibs-downloader libsnmp-dev
echo "Installing snmp snmpd snmp-mibs-downloader libsnmp-dev..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
snmp snmpd snmp-mibs-downloader libsnmp-dev install started" >> $LOGFILE 2>&1
apt-get install snmp snmpd snmp-mibs-downloader libsnmp-dev -y &>> $LOGFILE 2>&1

# install speedtest-cli
echo "Installing speedtest-cli..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
speedtest-cli install started" >> $LOGFILE 2>&1
apt-get install speedtest-cli -y &>> $LOGFILE 2>&1

# install Python Module requests via pip3
echo "Installing pip3 module requests..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module requests install started" >> $LOGFILE 2>&1
pip3 install requests &>> $LOGFILE 2>&1

# install Python Module mysqlclient via pip3
echo "Installing pip3 module mysqlclient..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module mysqlclient install started" >> $LOGFILE 2>&1
pip3 install mysqlclient &>> $LOGFILE 2>&1

# install Python Module lxml via pip3
echo "Installing pip3 module lxml..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module lxml install started" >> $LOGFILE 2>&1
pip3 install lxml &>> $LOGFILE 2>&1

# install Python Module cssselect via pip3
echo "Installing pip3 module cssselect..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module cssselect install started" >> $LOGFILE 2>&1
pip3 install cssselect &>> $LOGFILE 2>&1

# install Python Module python3-netsnmp via pip3
echo "Installing pip3 module python3-netsnmp..."
echo >> $LOGFILE 2>&1
echo "$(date "+%Y-%m-%d %T") : \
Python Module python3-netsnmp install started" >> $LOGFILE 2>&1
pip3 install python3-netsnmp &>> $LOGFILE 2>&1

# Therefore to set the hostname
echo "Setting Hostname..."
raspi-config nonint do_hostname $HOSTNAME

# Adding the crontab lines
echo "adding the crontab lines"
(sudo crontab -u pi -l ; echo "* * * * * cd /srv/PiMetric/monitoring && python3 monitoring.py") | crontab -u pi -
(sudo crontab -u pi -l ; echo "5 0 * * * cd /srv/PiMetric/monitoring && python3 db-manage.py") | crontab -u pi -

# At the end of the installation a reboot will be required.
echo "############################"
echo "All finished. Rebooting."
echo "############################"

# Reboot
sudo reboot
