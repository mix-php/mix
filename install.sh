#!/bin/bash

function getdir() {
    for element in `ls $1`
    do
        file=$1"/"$element
        if [ -f $file ]
        then
            bin_path=$1"/"$element
            sysbin_path="/usr/local/bin/"$element
            echo "chmod 777 $bin_path"
            chmod 777 $bin_path
            echo "rm -rf $sysbin_path"
            rm -rf $sysbin_path
            echo "ln -s $bin_path $sysbin_path"
            ln -s $bin_path $sysbin_path
        fi
    done
}

dirname=$(cd `dirname $0`; pwd)
getdir "$dirname/bin"

echo "Successful install to \"/usr/local/bin\""
