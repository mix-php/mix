<?php

/**
 * 一键安装
 * @author 刘健 <code.liu@qq.com>
 */

echo 'download mixphp zip ... ';
copy('https://github.com/mixstart/mixphp/archive/Beta2.zip', 'mixphp.zip');
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

echo 'download composer.phar (Wait a minute. It\'s a little slow) ... ';
copy('https://getcomposer.org/composer.phar', __DIR__ . '/' . $dirname . 'composer.phar');
echo 'ok' . PHP_EOL;

echo 'install complete' . PHP_EOL;
unlink(__FILE__);
