#!/usr/bin/env bash

#UPDATE APT REPO
apt-get update
apt-get dist-upgrade

#INSTALL TOOLS
apt-get install -y git apache2 php5

#CREATE WORKSPACE AND WEBROOT
rm -rf /var/www
ln -s /vagrant/webroot /var/www
