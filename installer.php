<?php

/**
 * 一键安装
 * @author 刘健 <coder.liu@qq.com>
 */


$url     = 'https://github.com/mixstart/mixphp/archive/Beta7.zip';
$zipfile = 'mixphp-' . basename($url);

// 下载
echo "Download {$url} ... ";
copy($url, $zipfile);
echo 'ok' . PHP_EOL;

// 解压
if (!extension_loaded('zip')) {
    echo 'Error: Zip extension is not enabled, please manually unzip.' . PHP_EOL;
    echo 'Zip file in "' . __DIR__ . DIRECTORY_SEPARATOR . $zipfile . '"' . PHP_EOL;
    exit;
}
echo 'Unzip ... ';
$zip = new ZipArchive;
$zip->open('mixphp.zip');
$dirname = $zip->getNameIndex(0);
$zip->extractTo(__DIR__ . '/');
$zip->close();
echo 'ok' . PHP_EOL;

// 清扫
echo 'Clean temp files ... ';
unlink('mixphp.zip');
echo 'ok' . PHP_EOL;

// 成功
echo 'Successfully installed in "' . __DIR__ . DIRECTORY_SEPARATOR . substr($dirname, 0, strlen($dirname) - 1) . '"' . PHP_EOL;
unlink(__FILE__);
