#!/usr/bin/env bash

#UPDATE APT REPO
apt-get update
apt-get dist-upgrade

#INSTALL TOOLS
apt-get install -y git apache2 php5

#CREATE WORKSPACE AND WEBROOT
rm -rf /home/vagrant/workspace
mkdir /home/vagrant/workspace
git clone https://github.com/djangoZa/idc-consultants-group.git /home/vagrant/workspace/idc-consultants-group
rm -rf /var/www
ln -s /home/vagrant/workspace/idc-consultants-group /var/www
