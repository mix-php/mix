<?php

namespace mix\web;

use mix\base\BaseObject;

/**
 * JSON 类
 * @author 刘健 <coder.liu@qq.com>
 */
class Json extends BaseObject
{

    // 编码
    public static function encode($array)
    {
        // 不转义中文、斜杠
        return json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
