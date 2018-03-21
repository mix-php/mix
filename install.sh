#!/bin/bash

dirname=$(cd `dirname $0`; pwd)

httpd_bin="/usr/local/bin/mix-httpd"
crontab_bin="/usr/local/bin/mix-crontab"
daemon_bin="/usr/local/bin/mix-daemon"
websocketd_bin="/usr/local/bin/mix-websocketd"

httpd_path="${dirname}/bin/mix-httpd"
crontab_path="${dirname}/bin/mix-crontab"
daemon_path="${dirname}/bin/mix-daemon"
websocketd_path="${dirname}/bin/mix-websocketd"

echo "rm -rf $httpd_bin"
rm -rf $httpd_bin
echo "rm -rf $crontab_bin"
rm -rf $crontab_bin
echo "rm -rf $daemon_bin"
rm -rf $daemon_bin
echo "rm -rf $websocketd_bin"
rm -rf $websocketd_bin

echo "ln -s $httpd_path $httpd_bin"
ln -s $httpd_path $httpd_bin
echo "ln -s $crontab_path $crontab_bin"
ln -s $crontab_path $crontab_bin
echo "ln -s $daemon_path $daemon_bin"
ln -s $daemon_path $daemon_bin
echo "ln -s $websocketd_path $websocketd_bin"
ln -s $websocketd_path $websocketd_bin

echo "chmod 777 $httpd_path"
chmod 777 $httpd_path
echo "chmod 777 $crontab_path"
chmod 777 $crontab_path
echo "chmod 777 $daemon_path"
chmod 777 $daemon_path
echo "chmod 777 $websocketd_path"
chmod 777 $websocketd_path

echo "Successful install to \"/usr/local/bin\""
