#!/bin/sh
echo "============`date +%F' '%T`==========="

php=/usr/local/bin/php
file=/project/bin/swoole.php
cmd=start
numprocs=1

getpid()
{
  docmd=`ps aux | grep ${file} | grep ${cmd} | grep -v 'grep' | grep -v '\.sh' | awk '{print $2}' | xargs`
  echo $docmd
}

getmpid()
{
  docmd=`ps -ef | grep ${file} | grep ${cmd} | grep -v 'grep' | grep -v '\.sh' | grep ' 1 ' | awk '{print $2}' | xargs`
  echo $docmd
}

start()
{
  pidstr=`getpid`
  if [ -n "$pidstr" ];then
    echo "running with pids $pidstr"
  else
     if [ $numprocs -eq 1 ];then
         $php $file $cmd > /dev/null 2>&1 &
     else
        i=0
        while(( $i<$numprocs ))
        do
            $php $file $cmd > /dev/null 2>&1 &
            let "i++"
        done
     fi
     sleep 1
     pidstr=`getpid`
     echo "start with pids $pidstr"
  fi
}

stop()
{
  pidstr=`getpid`
  if [ ! -n "$pidstr" ];then
     echo "not executed!"
     return
  fi
  mpidstr=`getmpid`
  if [ -n "$mpidstr" ];then
     pidstr=$mpidstr
  fi
  echo "kill $pidstr"
  kill $pidstr
}

restart()
{
  stop
  sleep 1
  start
}

case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  restart)
    restart
    ;;
esac
