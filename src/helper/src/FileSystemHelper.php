<?php

namespace Mix\Helper;

/**
 * Class FileSystemHelper
 * @package Mix\Helper
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileSystemHelper
{

    /**
     * 返回路径中的目录部分，正反斜杠linux兼容处理
     * @param $path
     * @return string
     */
    public static function dirname($path)
    {
        if (strpos($path, '\\') === false) {
            return dirname($path);
        }
        return str_replace('/', '\\', dirname(str_replace('\\', '/', $path)));
    }

    /**
     * 返回路径中的文件名部分，正反斜杠linux兼容处理
     * @param $path
     * @return string
     */
    public static function basename($path)
    {
        if (strpos($path, '\\') === false) {
            return basename($path);
        }
        return str_replace('/', '\\', basename(str_replace('\\', '/', $path)));
    }

    /**
     * 判断是否为绝对路径
     * @param $path
     * @return bool
     */
    public static function isAbsolute($path)
    {
        if (($position = strpos($path, './')) !== false && $position <= 2) {
            return false;
        }
        if (strpos($path, ':') !== false) {
            return true;
        }
        if (substr($path, 0, 1) === '/') {
            return true;
        }
        return false;
    }

}
