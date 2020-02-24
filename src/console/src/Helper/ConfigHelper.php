<?php

namespace Mix\Console\Helper;

/**
 * Class ConfigHelper
 * @package Mix\Console\Helper
 */
class ConfigHelper
{

    /**
     * 遍历配置目录，返回全部配置
     * @param string $path
     * @return array
     */
    public static function each(string $path)
    {
        if (is_file($path)) {
            return include $path;
        } else {
            $dir = $path;
        }
        $config = [];
        $dh     = @opendir($dir);
        if (!$dh) {
            throw new \RuntimeException(sprintf('Invalid path: %s', $path));
        }
        while (false !== ($file = readdir($dh))) {
            if (($file != '.') && ($file != '..')) {
                $full = $dir . '/' . $file;
                if (is_file($full)) {
                    $config = array_merge($config, include $full);
                }
            }
        }
        closedir($dh);
        return $config;
    }

}