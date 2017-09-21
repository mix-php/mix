<?php

echo '下载 MixPHP ...' . PHP_EOL;

copy('https://github.com/mixstart/mixphp/archive/Beta2.zip', 'mixphp.zip');

echo '解压 MixPHP ...' . PHP_EOL;
$zip = new ZipArchive;
$res = $zip->open('mixphp.zip');
$mixdir = $zip->getNameIndex(0);
$zip->extractTo(__DIR__ .'/');
$zip->close();

echo '清理临时文件' . PHP_EOL;
unlink('mixphp.zip');
unlink('installer.php');

echo '下载 Composer ...' . PHP_EOL;
copy('https://getcomposer.org/download/1.5.2/composer.phar', __DIR__. '/' . $mixdir . 'composer.phar');

echo '安装完成' . PHP_EOL;
