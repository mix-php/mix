<?php

namespace mix\web;

use mix\base\BaseObject;

/**
 * Captcha类
 * @author 刘健 <coder.liu@qq.com>
 */
class Captcha extends BaseObject
{

    // 宽度
    public $width = 100;

    // 高度
    public $height = 40;

    // 字集合
    public $wordSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // 字体文件
    public $fontFile = '';

    // 字体大小
    public $fontSize = 20;

    // 字数
    public $wordNumber = 4;

    // 角度随机
    public $angleRand = [-20, 20];

    // 字距
    public $xSpacing = 0.8;

    // Y轴随机
    public $yRand = [5, 15];

    // 文本
    protected $_text;

    // 内容
    protected $_content;

    // 生成
    public function generate()
    {
        $canvas     = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        $fontColor  = imagecolorallocate($canvas, 32, 64, 160);
        imagefill($canvas, 0, 0, $background);
        for ($i = 1; $i <= $this->wordNumber; $i++) {
            $word = iconv_substr($this->wordSet, floor(mt_rand(0, mb_strlen($this->wordSet, 'utf-8') - 1)), 1, 'utf-8');
            $this->_text .= $word;
            imagettftext($canvas, $this->fontSize, mt_rand($this->angleRand[0], $this->angleRand[1]), $this->fontSize * ($this->xSpacing * $i), $this->fontSize + mt_rand($this->yRand[0], $this->yRand[1]), $fontColor, $this->fontFile, $word);
        }
        imagesavealpha($canvas, true);
        ob_start();
        imagepng($canvas);
        imagedestroy($canvas);
        $this->_content = ob_get_clean();
    }

    // 获取文本
    public function getText()
    {
        return $this->_text;
    }

    // 获取内容
    public function getContent()
    {
        return $this->_content;
    }

}
