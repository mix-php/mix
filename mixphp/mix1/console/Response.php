<?php

namespace mix\console;

use mix\base\Component;

/**
 * Response组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Response extends Component
{

    // 格式值
    const FORMAT_RAW = -1;

    // 输出格式
    public $format = self::FORMAT_RAW;

    // 内容
    public $content = '';

    // 输出
    public function send()
    {
        $content = $this->content;
        is_null($content) and $content = '';
        if (is_array($content)) {
            switch ($this->format) {
                case self::FORMAT_RAW:
                    $content = 'Array';
                    break;
            }
        }
        if (is_scalar($content)) {
            echo $content;
        }
    }

}
