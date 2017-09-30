<?php

/**
 * 一键安装
 * @author 刘健 <coder.liu@qq.com>
 */

echo 'download mixphp zip ... ';
copy('https://github.com/mixstart/mixphp/archive/Beta2.zip', 'mixphp.zip');
//copy('https://github.com/mixstart/mixphp/archive/master.zip', 'mixphp.zip');
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

/*
echo 'download composer.phar (It\'s a little slow) ... ';
copy('https://getcomposer.org/composer.phar', __DIR__ . '/' . $dirname . 'composer.phar');
echo 'ok' . PHP_EOL;
*/

echo 'Successfully installed in "' . __DIR__ . DIRECTORY_SEPARATOR . substr($dirname,0,strlen($dirname)-1) . '"' . PHP_EOL;
unlink(__FILE__);
