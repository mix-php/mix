#!/bin/bash

for element in `find ./ -name mix-*`
do
    element=${element:2}
    dirname=$(cd `dirname $0`; pwd)
    file=$(basename $element)
    bin_path="$dirname/$element"
    sysbin_path="/usr/local/bin/"$file
    echo "chmod 777 $bin_path"
    chmod 777 $bin_path
    echo "rm -rf $sysbin_path"
    rm -rf $sysbin_path
    echo "ln -s $bin_path $sysbin_path"
    ln -s $bin_path $sysbin_path
done

echo "Successful install to \"/usr/local/bin\""
