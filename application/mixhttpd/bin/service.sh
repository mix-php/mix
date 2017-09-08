#!/usr/bin/env bash

# 重启所有worker进程
if [ $1 == "worker" -a $2 == "reload" ]
then
    ps -ef | grep mixhttpd | awk 'NR==1{print $2}' | xargs -n1 kill -USR1
    echo "mixhttpd worker reload success"
fi

# 重启所有task进程
if [ $1 == "task" -a $2 == "reload" ]
then
    ps -ef | grep mixhttpd | awk 'NR==1{print $2}' | xargs -n1 kill -USR2
    echo "mixhttpd task reload success"
fi

# 关闭服务
if [ $1 == "stop" ]
then
    ps -ef | grep mixhttpd | awk 'NR==1{print $2}' | xargs -n1 kill
    echo "mixhttpd stop success"
fi
