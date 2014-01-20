#!/usr/bin/env bash

#UPDATE APT REPO
apt-get update
apt-get dist-upgrade

#INSTALL SERVICES
apt-get install -y apache2 php5 curl php5-curl

#CREATE WWEBROOT AND TEMP DIRECTORY
rm -rf /var/www
ln -s /vagrant/webroot /var/www
mkdir /tmp/idc-consultants-group
chown www-data:www-data /tmp/idc-consultants-group

#RESTART APACHE
service apache2 restart