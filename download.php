<?php

/**
 * 一键下载
 * @author 刘健 <coder.liu@qq.com>
 */

$url     = 'https://github.com/mixstart/mixphp/releases/download/v1.1.1/mixphp-full-v1.1.1.zip';
$zipfile = basename($url);

// 下载
echo "download {$url} ... ";
copy($url, $zipfile);
echo 'ok' . PHP_EOL;

// 解压
if (!extension_loaded('zip')) {
    echo 'Error: Zip extension is not enabled, please manually unzip.' . PHP_EOL;
    echo 'Zip file in "' . __DIR__ . DIRECTORY_SEPARATOR . $zipfile . '"' . PHP_EOL;
    exit;
}
echo 'unzip ... ';
$zip = new ZipArchive;
$zip->open($zipfile);
$dirname = $zip->getNameIndex(0);
$zip->extractTo(__DIR__ . '/');
$zip->close();
echo 'ok' . PHP_EOL;

// 清扫
echo 'clean temp files ... ';
unlink($zipfile);
unlink(__FILE__);
echo 'ok' . PHP_EOL;

// 成功
echo 'Successful download to "' . __DIR__ . DIRECTORY_SEPARATOR . substr($dirname, 0, strlen($dirname) - 1) . '"' . PHP_EOL;
