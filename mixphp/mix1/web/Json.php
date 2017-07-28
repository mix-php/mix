<?php

/**
 * View类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

class Json
{

    // 编码
    public static function encode($array)
    {
        // 不转义中文、斜杠
        return json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
