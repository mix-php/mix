<?php

namespace mix\web;

use yii\base\Object;

/**
 * View类
 * @author 刘健 <coder.liu@qq.com>
 */
class Jsonp extends Object
{

    // callback名称
    public $callbackName = 'callback';

    // 编码
    public function encode($array)
    {
        // 不转义中文、斜杠
        $jsonString = Json::encode($array);
        if (empty($_GET[$this->callbackName])) {
            return $jsonString;
        }
        return $_GET[$this->callbackName] . '(' . $jsonString . ')';
    }

}
