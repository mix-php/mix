<?php

/**
 * 一键安装
 * @author 刘健 <coder.liu@qq.com>
 */

//$url = 'https://github.com/mixstart/mixphp/archive/master.zip';
$url = 'https://github.com/mixstart/mixphp/archive/Beta3.zip';
echo "download {$url} ... ";
copy($url, 'mixphp.zip');
echo 'ok' . PHP_EOL;

echo 'unzip ... ';
$zip = new ZipArchive;
$zip->open('mixphp.zip');
$dirname = $zip->getNameIndex(0);
$zip->extractTo(__DIR__ . '/');
$zip->close();
echo 'ok' . PHP_EOL;

echo 'clean temp files ... ';
unlink('mixphp.zip');
echo 'ok' . PHP_EOL;

echo 'Successfully installed in "' . __DIR__ . DIRECTORY_SEPARATOR . substr($dirname, 0, strlen($dirname) - 1) . '"' . PHP_EOL;
unlink(__FILE__);
