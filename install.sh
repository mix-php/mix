#!/bin/bash

dirname=$(cd `dirname $0`; pwd)

httpd_bin="/usr/local/bin/mix-httpd"
httpd_path="${dirname}/server/httpd/mix-httpd"
daemon_bin="/usr/local/bin/mix-daemon"
daemon_path="${dirname}/task/daemon/mix-daemon"
crontab_bin="/usr/local/bin/mix-crontab"
crontab_path="${dirname}/task/crontab/mix-crontab"

echo "rm -rf $httpd_bin"
rm -rf $httpd_bin
echo "rm -rf $daemon_bin"
rm -rf $daemon_bin
echo "rm -rf $crontab_bin"
rm -rf $crontab_bin

echo "ln -s $httpd_path $httpd_bin"
ln -s $httpd_path $httpd_bin
echo "ln -s $daemon_path $daemon_bin"
ln -s $daemon_path $daemon_bin
echo "ln -s $crontab_path $crontab_bin"
ln -s $crontab_path $crontab_bin

echo "chmod 777 $httpd_path"
chmod 777 $httpd_path
echo "chmod 777 $daemon_path"
chmod 777 $daemon_path
echo "chmod 777 $crontab_path"
chmod 777 $crontab_path

echo "Successful install to \"/usr/local/bin\""
