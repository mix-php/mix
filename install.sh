#!/bin/bash

install_local() {
    for element in `find ./ -type f -name mix-*`
    do
        element=${element:2}
        dirname=$(cd `dirname $0`; pwd)
        file=$(basename $element)
        bin_path="$dirname/$element"
        ln_bin_path="/usr/local/bin/"$file
        echo "chmod 777 $bin_path"
        chmod 777 $bin_path
        echo "rm -rf $ln_bin_path"
        rm -rf $ln_bin_path
        echo "ln -s $bin_path $ln_bin_path"
        ln -s $bin_path $ln_bin_path
    done
    echo "Successful install to \"/usr/local/bin\""
}

case "$1" in
    *)
        install_local
        ;;
esac
