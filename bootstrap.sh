#!/usr/bin/env bash

#UPDATE APT REPO
apt-get update
apt-get dist-upgrade

#INSTALL SERVICES
apt-get install -y apache2 php5 curl php5-curl php5-gd

#CREATE TEMP DIRECTORY
mkdir /tmp/idc-consultants-group
chmod -R 1777 /tmp

#RESTART APACHE
service apache2 restart