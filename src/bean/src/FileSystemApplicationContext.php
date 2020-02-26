<?php

namespace Mix\Bean;

/**
 * Class FileSystemApplicationContext
 * @package Mix\Bean
 * @deprecated 废弃，统一都使用 ApplicationContext
 */
class FileSystemApplicationContext extends ApplicationContext
{

    /**
     * FileSystemApplicationContext constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct(static::getConfig($path));
    }

    /**
     * 获取配置
     * @param string $dir
     * @return array
     */
    protected static function getConfig(string $path)
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
